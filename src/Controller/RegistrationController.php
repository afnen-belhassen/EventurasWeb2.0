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
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Bundle\SecurityBundle\Security;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register( MailerInterface $mailer,Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher, RoleRepository $roleRepository): Response
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
        
            $email = (new Email())
            ->from("az.backup04@gmail.com")
            ->to(addresses: "islemazzouz04@gmail.com") // Remplacer par l'email du client
            ->subject('NEW ORGANISATEUR')
            ->text(sprintf(
                "Un nouvel utilisateur a demandé un compte organisateur.\n\nNom: %s %s\nEmail: %s\nUsername: %s",
                $user->getUserFirstname(),
                $user->getUserLastname(),
                $user->getUserEmail(),
                $user->getUserUsername()
            ));
    
        // Envoyer l'email
        $mailer->send($email);
    
        // Message de succès
        $this->addFlash('success', 'Your orders have been successfully sent via email.');
    
    
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
//+Birhtday celebration

    #[Route('/homeProfile', name: 'homeProfile', methods: ['GET', 'POST'])]
    public function home(Security $security): Response
    {
        $user = $security->getUser();
    
        $isBirthday = false;
    
        if ($user && $user->getUserBirthday()) {
            $today = new \DateTime();
            $birthday = $user->getUserBirthday();
            // On compare jour et mois
            if ($today->format('m-d') === $birthday->format('m-d')) {
                $isBirthday = true;
            }
        }
    
        return $this->render('security/index.html.twig', [
            'isBirthday' => $isBirthday,
        ]);
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

    #[Route('/forgot-password', name: 'app_forgot_password')]
public function forgotPassword(Request $request, EntityManagerInterface $em, MailerInterface $mailer): Response
{
    if ($request->isMethod('POST')) {
        $email = $request->request->get('email');
        $user = $em->getRepository(Users::class)->findOneBy(['userEmail' => $email]);

        if ($user) {
            $token = bin2hex(random_bytes(32));
            $user->setResetToken($token);
            $em->flush();

            $resetUrl = $this->generateUrl('app_reset_password', ['token' => $token], UrlGeneratorInterface::ABSOLUTE_URL);

            $emailMessage = (new Email())
                ->from('az.backup04@gmail.com')
                ->to($user->getUserEmail())
                ->subject('Réinitialisation du mot de passe')
                ->text("Cliquez ici pour réinitialiser votre mot de passe : $resetUrl");

            $mailer->send($emailMessage);
        }

        $this->addFlash('success', 'Si votre email existe, un lien de réinitialisation vous a été envoyé.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/forgot_password.html.twig');

}
#[Route('/reset-password/{token}', name: 'app_reset_password')]
public function resetPassword(string $token, Request $request, EntityManagerInterface $em, UserPasswordHasherInterface $hasher): Response
{
    $user = $em->getRepository(Users::class)->findOneBy(['resetToken' => $token]);

    if (!$user) {
        throw $this->createNotFoundException('Lien invalide.');
    }

    if ($request->isMethod('POST')) {
        $newPassword = $request->request->get('password');
        $hashed = $hasher->hashPassword($user, $newPassword);
        $user->setUserPassword($hashed);
        $user->setResetToken(null); // Invalide le token

        $em->flush();
        $this->addFlash('success', 'Mot de passe mis à jour.');
        return $this->redirectToRoute('app_login');
    }

    return $this->render('security/reset_password.html.twig', [
        'token' => $token,
    ]);
}
}
