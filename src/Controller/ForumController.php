<?php

namespace App\Controller;

use App\Entity\Post;
use App\Entity\Comment;
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
} 