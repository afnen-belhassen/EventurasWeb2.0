<?php
namespace App\Controller;

use App\Entity\Users;
use App\Repository\RoleRepository;
use App\Form\RegistrationFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Endroid\QrCode\Builder\BuilderInterface;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\PngWriter;
use Endroid\QrCode\QrCode ;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, RoleRepository $roleRepository): Response
{
    $user = new Users();
    
   
    
    $form = $this->createForm(RegistrationFormType::class, $user);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {

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
        // Hash du mot de passe
        $hashedPassword = $passwordHasher->hashPassword($user, $user->getUserPassword());
        $user->setUserPassword($hashedPassword);
        $role = $form->get('role')->getData();

        if ($role && $role->getRoleName() === 'ROLE_ORGANISATEUR') {
            $user->setStatut(0);
        } else {
            $user->setStatut(1);
        }
        
        // Persist the user and redirect
        $entityManager->persist($user);
        $entityManager->flush();
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

        // Redirection vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    return $this->render('registration/register.html.twig', [
        'registrationForm' => $form->createView(),
    ]);
}


    #[Route('/login', name: 'app_login', methods: ['GET', 'POST'])]
    public function login(): Response
    {
        return $this->render('security/login.html.twig');
    }

    #[Route('/homeProfile', name: 'homeProfile', methods: ['GET', 'POST'])]
    public function home(): Response
    {
        return $this->render('security/index.html.twig');
    }


    #[Route('/ProfileOrganisateur', name: 'ProfileOrganisateur', methods: ['GET', 'POST'])]
    public function home_organisateur(): Response
    {
        return $this->render('Profile/Profile_Organisateur.html.twig');
    }


    #[Route('/ProfileAdmin', name: 'ProfileAdmin', methods: ['GET', 'POST'])]
    public function home_admin(): Response
    {
        return $this->render('users/index.html.twig');
    }


   /**
     * @Route("/logout", name="logout")
     */
    public function logout()
    {
        return new RedirectResponse($this->generateUrl('app_login'));
    }

    
}
