<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
use App\Entity\Like;
use App\Entity\BadWord;
use App\Repository\LikeRepository;
use App\Repository\PostRepository;
use App\Repository\CommentRepository;
use App\Repository\BadWordRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

class ForumController extends AbstractController
{
    private $uploadDirectory;

    public function __construct(string $uploadDirectory)
    {
        $this->uploadDirectory = $uploadDirectory;
    }

    #[Route('/forum', name: 'app_forum')]
    public function index(PostRepository $postRepository,EntityManagerInterface $em,BadWordRepository $badWordRepository): Response
    {

        // Récupération de l'utilisateur courant
        //$user = $this->getUser();
        $user = 1;
        
        // Définition de la plage horaire pour "aujourd'hui"
        $todayStart = new \DateTimeImmutable('today midnight');
        $todayEnd   = new \DateTimeImmutable('tomorrow midnight');

        // Calcul du nombre de posts effectués aujourd'hui par l'utilisateur
        $postedToday = $em->getRepository(Post::class)
            ->createQueryBuilder('p')
            ->select('COUNT(p.id)')
            ->where('p.user_id = :user')
            ->andWhere('p.created_at BETWEEN :start AND :end')
            ->setParameter('user', $user)
            ->setParameter('start', $todayStart)
            ->setParameter('end', $todayEnd)
            ->getQuery()
            ->getSingleScalarResult();

        $badWords = $badWordRepository->findAll();
        $badWordsArray = array_map(fn($bw) => $bw->getWord(), $badWords);

        $posts = $postRepository->findAllOrderedByDate();

        // Récupération du repository des likes
        $likeRepository = $em->getRepository(\App\Entity\Like::class);
        
        // Pour chaque post, ajouter les propriétés likeCount et likedByCurrent
        foreach ($posts as $post) {
            // Calcul du nombre de likes pour ce post
            $likeCount = (int)$likeRepository->createQueryBuilder('l')
                ->select('COUNT(l.id)')
                ->andWhere('l.postId = :postId')
                ->setParameter('postId', $post->getId())
                ->getQuery()
                ->getSingleScalarResult();
            
            // Vérification si le post a déjà été liké par l'utilisateur courant (user 1)
            $likedByCurrent = $likeRepository->findOneBy([
                'postId' => $post->getId(),
                'userId' => $user,
            ]) !== null;

            // Ajout dynamique des propriétés au post
            $post->likeCount = $likeCount;
            $post->likedByCurrent = $likedByCurrent;
        }

        return $this->render('forum/index.html.twig', [
            'posts' => $posts, 'postedToday' => $postedToday,'badWords' => $badWordsArray,
        ]);
    }

    #[Route('/forum/create', name: 'app_forum_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post = new Post();
        $post->setTitle($request->request->get('title'));
        $post->setContent($request->request->get('content'));
        $post->setUserId(1); // Hardcoded user_id
        $post->setCreatedAt(new \DateTime());

        // Handle image upload
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->uploadDirectory,
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if something happens during file upload
            }

            $post->setImagePath($newFilename);
        }

        $entityManager->persist($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/{id}/edit', name: 'app_forum_edit', methods: ['POST'])]
    public function edit(Request $request, Post $post, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $post->setTitle($request->request->get('title'));
        $post->setContent($request->request->get('content'));
        
        // Handle image upload
        $imageFile = $request->files->get('image');
        if ($imageFile) {
            // Delete old image if exists
            if ($post->getImagePath()) {
                $oldImagePath = $this->uploadDirectory.'/'.$post->getImagePath();
                if (file_exists($oldImagePath)) {
                    unlink($oldImagePath);
                }
            }

            $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
            $safeFilename = $slugger->slug($originalFilename);
            $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

            try {
                $imageFile->move(
                    $this->uploadDirectory,
                    $newFilename
                );
            } catch (FileException $e) {
                // Handle exception if something happens during file upload
            }

            $post->setImagePath($newFilename);
        }
        
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/{id}/delete', name: 'app_forum_delete', methods: ['POST'])]
    public function delete(Post $post, EntityManagerInterface $entityManager): Response
    {
        // Delete image file if exists
        if ($post->getImagePath()) {
            $imagePath = $this->uploadDirectory.'/'.$post->getImagePath();
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
        }

        $entityManager->remove($post);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/{id}/comment', name: 'app_forum_comment', methods: ['POST'])]
    public function addComment(Request $request, Post $post, EntityManagerInterface $entityManager): Response
    {
        $comment = new Comment();
        $comment->setContent($request->request->get('content'));
        $comment->setUserId(1); // Hardcoded user_id
        $comment->setCreatedAt(new \DateTime());
        $comment->setPost($post);

        $entityManager->persist($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/comment/{id}/delete', name: 'app_forum_comment_delete', methods: ['POST'])]
    public function deleteComment(Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($comment);
        $entityManager->flush();

        return $this->redirectToRoute('app_forum');
    }

    #[Route('/forum/comment/{id}/edit', name: 'app_forum_comment_edit', methods: ['POST'])]
    public function editComment(Request $request, Comment $comment, EntityManagerInterface $entityManager): Response
    {
        $content = $request->request->get('content');
        if ($content) {
            $comment->setContent($content);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_forum');
    }

    #[Route("/forum/like/{postId}", name:"app_forum_like", methods:['POST'])]
    public function toggleLike(int $postId, EntityManagerInterface $em, Request $request): JsonResponse
    {
        // Pour l'instant, on considère toujours l'utilisateur avec l'id 1
        $userId = 1;

        // Rechercher un like existant pour ce post par cet utilisateur
        $likeRepository = $em->getRepository(Like::class);
        $existingLike = $likeRepository->findOneBy([
            'postId' => $postId,
            'userId' => $userId,
        ]);

        if ($existingLike) {
            // Si déjà liké, annuler le like
            $em->remove($existingLike);
            $liked = false;
        } else {
            // Sinon, créer un nouveau like
            $like = new Like();
            $like->setPostId($postId);
            $like->setUserId($userId);
            $like->setLikedAt(new \DateTimeImmutable());
            $em->persist($like);
            $liked = true;
        }

        $em->flush();

        // Recompter le nombre de likes pour ce post
        $likeCount = (int) $likeRepository->createQueryBuilder('l')
            ->select('COUNT(l.id)')
            ->andWhere('l.postId = :postId')
            ->setParameter('postId', $postId)
            ->getQuery()
            ->getSingleScalarResult();

        return new JsonResponse([
            'liked' => $liked,
            'likeCount' => $likeCount,
        ]);
    }

    #[Route('/admin/forum', name: 'app_admin_forum')]
    public function adminDashboard(
        PostRepository $postRepository,
        CommentRepository $commentRepository,
        BadWordRepository $badWordRepository
    ): Response {
        // Vérification des droits (à adapter selon votre système d'authentification)
        // if (!$this->isGranted('ROLE_ADMIN')) {
        //     throw $this->createAccessDeniedException();
        // }

        return $this->render('forum/admin.html.twig', [
            'stats' => [
                'totalPosts' => $postRepository->count([]),
                'totalComments' => $commentRepository->count([]),
            ],
            'badWords' => $badWordRepository->findAll(),
            'allPosts' => $postRepository->findAll(),
            'allComments' => $commentRepository->findAll(),
        ]);
    }

    #[Route('/admin/badword/add', name: 'app_admin_add_badword', methods: ['POST'])]
    public function addBadWord(Request $request, EntityManagerInterface $em): Response
    {
        $word = $request->request->get('word');
        
        $badWord = new BadWord();
        $badWord->setWord($word);
        
        $em->persist($badWord);
        $em->flush();

        return $this->redirectToRoute('app_admin_forum');
    }

    #[Route('/admin/badword/{id}/delete', name: 'app_admin_delete_badword', methods: ['POST'])]
    public function deleteBadWord(BadWord $badWord, EntityManagerInterface $em): Response
    {
        $em->remove($badWord);
        $em->flush();

        return $this->redirectToRoute('app_admin_forum');
    }

    #[Route('/admin/post/{id}/delete', name: 'app_admin_delete_post', methods: ['POST'])]
    public function adminDeletePost(Post $post, EntityManagerInterface $em): Response
    {
        // Suppression des commentaires associés
        foreach ($post->getComments() as $comment) {
            $em->remove($comment);
        }
        
        $em->remove($post);
        $em->flush();

        return $this->redirectToRoute('app_admin_forum');
    }

    #[Route('/admin/comment/{id}/delete', name: 'app_admin_delete_comment', methods: ['POST'])]
    public function adminDeleteComment(Comment $comment, EntityManagerInterface $em): Response
    {
        $em->remove($comment);
        $em->flush();

        return $this->redirectToRoute('app_admin_forum');
    }
} 
