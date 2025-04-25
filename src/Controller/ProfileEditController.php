<?php

namespace App\Controller;

use App\Entity\Users;
use App\Form\UsersMofiType;
use App\Repository\RoleRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProfileEditController extends AbstractController
{
   
    #[Route('/editprofile/{id}/', name: 'app_users_edit_profile_par', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
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
            $user->setRole($role);
            $em->flush();

            return $this->redirectToRoute('homeProfile', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('security/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }
    #[Route('/EditOrganisateur/{id}/', name: 'edit_profile_organisateur', methods: ['GET', 'POST'], requirements: ['id' => '\d+'])]
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
            $user->setRole($role);
            $em->flush();

            return $this->redirectToRoute('ProfileOrganisateur', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('Profile/EditProfileOrganisateur.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    #[Route(path: '/id')]


    public function someAction(): Response
{
    $user = $this->getUser(); 
    $userId =  $user->getUserId() ;

    return $this->render('Profile/iduseraffiche.html.twig', [
        'userId' => $userId,
    ]);
}


    }

