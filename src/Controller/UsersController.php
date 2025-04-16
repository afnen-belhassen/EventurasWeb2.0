<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersType;
use App\Form\UsersMofiType;
use App\Repository\RoleRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode ;
#[Route('/user')]

class UsersController extends AbstractController
{
    #[Route('/app_users_index', name: 'app_users_index', methods: ['GET'])]
    public function index(UsersRepository $usersRepository, PaginatorInterface $paginator, Request $request): Response
    {
        $query = $usersRepository->createQueryBuilder('u')->getQuery();
        $pagination = $paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('users/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    #[Route('/new_users', name: 'app_users_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $em,UserPasswordHasherInterface $passwordHasher): Response
    {
        $user = new Users();
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $hashedPassword = $passwordHasher->hashPassword($user, $user->getUserPassword());
$user->setUserPassword($hashedPassword);
$pictureFile = $form->get('userPicture')->getData();
if ($pictureFile) {
    $newFilename = uniqid().'.'.$pictureFile->guessExtension();
    try {
        $pictureFile->move(
            $this->getParameter('users_pictures_directory'),
            $newFilename
        );
    } catch (FileException $e) {
        $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
    }
    $user->setUserPicture($newFilename);
}

            $em->persist($user);
            $em->flush();

            // --- Génération du QR code sans autowiring ---
            $data = sprintf(
                "Username: %s\nEmail: %s\nFirst name: %s\nLast name: %s\nBirthday: %s\nGender: %s\nPhone: %s\nLevel: %s\nRole: %s",
                $user->getUserUsername(),
                $user->getUserEmail(),
                $user->getUserFirstname(),
                $user->getUserLastname(),
                $user->getUserBirthday()?->format('Y-m-d') ?? 'N/A',
                $user->getUserGender(),
                $user->getUserPhonenumber(),
                $user->getUserLevel(),
                $user->getRole()
            );

            $qrCode = QrCode::create($data)
            ->setEncoding(new Encoding('UTF-8'))
            ->setErrorCorrectionLevel(ErrorCorrectionLevel::High) 
            ->setSize(300)
            ->setMargin(10);

            $writer = new PngWriter();
            $result = $writer->write($qrCode);

            $qrDir = $this->getParameter('kernel.project_dir').'/public/uploads/qrcodes';
            if (!is_dir($qrDir)) {
                mkdir($qrDir, 0755, true);
            }
            $qrFilename = $user->getUserId().'.png';
            $result->saveToFile($qrDir.'/'.$qrFilename);

            // Stockage du nom en flashbag pour affichage après redirection
            $this->addFlash('qr_code_filename', $qrFilename);
            // -------------------------------------

            return $this->redirectToRoute('app_users_index');
        }

        return $this->renderForm('users/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    

    #[Route('/{id}/showuser', name: 'app_users_show', methods: ['GET'], requirements: ['id' => '\d+'])]
    public function show(Users $user): Response
    {
        return $this->render('users/show.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/{id}/edit/user', name: 'app_users_edit', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function edit(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UsersType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Même gestion de l'upload qu'en création
            $pictureFile = $form->get('userPicture')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                try {
                    $pictureFile->move(
                        $this->getParameter('users_pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                }
                $user->setUserPicture($newFilename);
            }

            $em->flush();

            return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('users/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_users_delete', methods: ['POST'], requirements: ['id' => '\d+'])]
    public function delete(Request $request, Users $user, EntityManagerInterface $em): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getUserId(), $request->request->get('_token'))) {
            $em->remove($user);
            $em->flush();
        }
    
        return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
    }
   
    #[Route('/{id}/editprofile', name: 'app_users_edit_profile', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editprofileparticipant(RoleRepository $roleRepository,Request $request, Users $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UsersMofiType::class, $user);
        $form->handleRequest($request);
        $role = $roleRepository->findOneBy(criteria: ['roleName' => 'ROLE_PARTICIPANT']);
        if (!$role) {
            throw new \Exception('Role "participant" not found');
        }
        if ($form->isSubmitted() && $form->isValid()) {

            // Même gestion de l'upload qu'en création
            $pictureFile = $form->get('userPicture')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                try {
                    $pictureFile->move(
                        $this->getParameter('users_pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                }
                $user->setUserPicture($newFilename);
            }
            
            $user->setRole($role);
            $user->setUserRole('PARTICIPANT');
            $em->flush();

            return $this->redirectToRoute('homeProfile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('security/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/EditProfileOrganisateur', name: 'app_users_edit_profile_organisateur', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
    public function editprofileorganisateur(RoleRepository $roleRepository,Request $request, Users $user, EntityManagerInterface $em): Response
    {
        $form = $this->createForm(UsersMofiType::class, $user);
        $form->handleRequest($request);
        $role = $roleRepository->findOneBy(criteria: ['roleName' => 'ROLE_ORGANISATEUR']);
        if (!$role) {
            throw new \Exception('Role "" not found');
        }
        if ($form->isSubmitted() && $form->isValid()) {

            // Même gestion de l'upload qu'en création
            $pictureFile = $form->get('userPicture')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                try {
                    $pictureFile->move(
                        $this->getParameter('users_pictures_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                }
                $user->setUserPicture($newFilename);
            }
            
            $user->setRole($role);
            $user->setUserRole('ORGANISATEUR');
            $em->flush();

            return $this->redirectToRoute('homeProfile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Profile/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/{id}/edit/status', name: 'app_users_edit_status', methods: ['GET'])]
        public function edit_status(Users $user, EntityManagerInterface $em): Response
            {
                // Set the status to 1
                $user->setStatut(1); // Update the status to 1 (or any other value you need)

                // Persist the changes and flush to the database
                $em->flush(); // Commit the change to the database

                // Redirect to the users list (or any other route you want after the update)
                return $this->redirectToRoute('app_users_index', [], Response::HTTP_SEE_OTHER);
            }

    }

