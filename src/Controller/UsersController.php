<?php

namespace App\Controller;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use App\Entity\Users;
use App\Form\UsersType;
use App\Form\UsersMofiType;
use App\Repository\RoleRepository;
use App\Repository\UsersRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode ;
use Symfony\Component\HttpFoundation\JsonResponse;

use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
#[Route('/user')]

class UsersController extends AbstractController
{
    #[Route('/app_users_index', name: 'app_users_index', methods: ['GET'])]
public function index(UsersRepository $usersRepository, RoleRepository $roleRepository, PaginatorInterface $paginator, Request $request): Response
{
    $search = $request->query->get('search');
    $roleId = $request->query->get('role');
    $statut = $request->query->get('statut');

    $queryBuilder = $usersRepository->createQueryBuilder('u');

    if ($search) {
        $queryBuilder->andWhere('u.userUsername LIKE :search OR u.userEmail LIKE :search')
                     ->setParameter('search', '%' . $search . '%');
    }

    if ($roleId) {
        $roleEntity = $roleRepository->find($roleId);
        if ($roleEntity) {
            $queryBuilder->andWhere('u.role = :role')
                         ->setParameter('role', $roleEntity);
        }
    }

    if ($statut !== null && $statut !== '') {
        $queryBuilder->andWhere('u.statut = :statut')
                     ->setParameter('statut', $statut);
    }

    $query = $queryBuilder->getQuery();

    $pagination = $paginator->paginate(
        $query,
        $request->query->getInt('page', 1),
        5
    );

    $roles = $roleRepository->findAll();

    // Gestion AJAX (rechargement partiel)
    if ($request->isXmlHttpRequest()) {
        return $this->render('users/index.html.twig', [
            'pagination' => $pagination,
            'roles' => $roles,
        ]);
    }

    // Rendu complet
    return $this->render('users/index.html.twig', [
        'pagination' => $pagination,
        'roles' => $roles,
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
    public function edit_status(int $id, UsersRepository $usersRepository, EntityManagerInterface $em, MailerInterface $mailer): Response
    {
        // Recherche de l'utilisateur par ID
        $user = $usersRepository->find($id);
    
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        // Mise à jour du statut
        $user->setStatut(1);
    
        // Préparation de l'e-mail
        $email = (new Email())
            ->from("az.backup04@gmail.com")
            ->to($user->getUserEmail()) // Adresse e-mail récupérée dynamiquement
            ->subject('Validation de votre compte Organisateur')
            ->text(sprintf(
                "Bonjour %s %s,\n\nVotre compte organisateur a été validé avec succès.\nNom d'utilisateur : %s\nMerci de votre confiance.",
                $user->getUserFirstname(),
                $user->getUserLastname(),
                $user->getUserUsername()
            ));
    
        // Envoi de l'e-mail
        $mailer->send($email);
    
        // Sauvegarde des modifications
        $em->flush();
    
        // Redirection vers la liste des utilisateurs
        return $this->redirectToRoute('app_dashboard', [], Response::HTTP_SEE_OTHER);
    }
    //Notifications
    
            #[Route('/admin/notifications', name: 'admin_notifications', methods: ['GET'])]
public function notifications(EntityManagerInterface $entityManager): JsonResponse
{
    $pendingOrganisateurs = $entityManager->getRepository(Users::class)->findBy([
        'statut' => 0
    ]);

    $data = [];

    foreach ($pendingOrganisateurs as $user) {
        $data[] = [
            'id' => $user->getUserId(),
            'username' => $user->getUserUsername(),
            'email' => $user->getUserEmail()
        ];
    }

    return $this->json([
        'count' => count($data),
        'users' => $data
    ]);
}
//Fichier Excel

#[Route('/users/export', name:'app_users_export')]
 
public function export(UsersRepository $userRepository): Response
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    $sheet->setCellValue('A1', 'ID');
    $sheet->setCellValue('B1', 'Username');
    $sheet->setCellValue('C1', 'Email');
    $sheet->setCellValue('D1', 'Role');

    $users = $userRepository->findAll();  // ou ta requête filtrée
    $row = 2;

    foreach ($users as $user) {
        $sheet->setCellValue('A' . $row, $user->getUserId());
        $sheet->setCellValue('B' . $row, $user->getUserUsername());
        $sheet->setCellValue('C' . $row, $user->getUserEmail());
        $sheet->setCellValue('D' . $row, $user->getRole());
        $row++;
    }

    $writer = new Xlsx($spreadsheet);
    $temp_file = tempnam(sys_get_temp_dir(), 'users_export');
    $writer->save($temp_file);

    return $this->file($temp_file, 'users_list.xlsx', ResponseHeaderBag::DISPOSITION_INLINE);
}

//Statistics

#[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
public function dashboard(UsersRepository $usersRepository, RoleRepository $roleRepository): Response
{
    // Récupération des rôles en tant qu'entités
    $roleOrganisateur = $roleRepository->findOneBy(['roleName' => 'ROLE_ORGANISATEUR']);
    $roleParticipant = $roleRepository->findOneBy(['roleName' => 'ROLE_PARTICIPANT']);

    // Sécurité : vérification que les rôles existent
    if (!$roleOrganisateur || !$roleParticipant) {
        throw $this->createNotFoundException('Les rôles ROLE_ORGANISATEUR ou ROLE_PARTICIPANT sont introuvables en base.');
    }

    // Nombre total d'utilisateurs
    $totalUsers = $usersRepository->count([]);

    // Nombre d'organisateurs et de participants
    $organisateurs = $usersRepository->count(['role' => $roleOrganisateur]);
    $participants = $usersRepository->count(['role' => $roleParticipant]);

    // Statistiques par sexe
    $maleCount = $usersRepository->count(['userGender' => 'male']);
    $femaleCount = $usersRepository->count(['userGender' => 'female']);
    $otherGenderCount = $usersRepository->count(['userGender' => 'other']);

    // Statistiques par statut d'organisateur
    $organisateurStatut1 = $usersRepository->count([
        'role' => $roleOrganisateur,
        'statut' => 1
    ]);

    $organisateurStatut0 = $usersRepository->count([
        'role' => $roleOrganisateur,
        'statut' => 0
    ]);

    // Statistiques par tranches d'âge
    $age18_25 = $usersRepository->countAgeGroup(18, 25);
    $age26_35 = $usersRepository->countAgeGroup(26, 35);
    $age36_50 = $usersRepository->countAgeGroup(36, 50);
    $age50plus = $usersRepository->countAgeGroup(51, 100);

    return $this->render('dashbord/index.html.twig', [
        'totalUsers' => $totalUsers,
        'organisateurs' => $organisateurs,
        'participants' => $participants,
        'maleCount' => $maleCount,
        'femaleCount' => $femaleCount,
        'otherGenderCount' => $otherGenderCount,
        'organisateurStatut1' => $organisateurStatut1,
        'organisateurStatut0' => $organisateurStatut0,
        'ageGroups' => [
            '18-25' => $age18_25,
            '26-35' => $age26_35,
            '36-50' => $age36_50,
            '50+' => $age50plus,
        ]
    ]);
}


    }

