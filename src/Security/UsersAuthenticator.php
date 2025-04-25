<?php
// src/Security/UsersAuthenticator.php

namespace App\Security;

use App\Entity\Users;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Passport;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\Credentials\PasswordCredentials;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Bundle\SecurityBundle\Security;

class UsersAuthenticator extends AbstractAuthenticator
{
    private $entityManager;
    private $security;

    public function __construct(EntityManagerInterface $entityManager, Security $security)
    {
        $this->entityManager = $entityManager;
        $this->security = $security;
    }

    public function supports(Request $request): ?bool
    {
        // Support only POST requests to /login
        return $request->isMethod('POST') && $request->getPathInfo() === '/login';
    }

    public function authenticate(Request $request): Passport
    {
        $email = $request->request->get('email');
        $password = $request->request->get('password');

        // Recherche l'utilisateur dans la base de données
        $user = $this->entityManager->getRepository(Users::class)->findOneBy(['userEmail' => $email]);

        // Vérification de l'existence de l'utilisateur
        if (!$user) {
            throw new AuthenticationException('Invalid credentials. Please try again. If you are an organizer, your account is not yet validated.');
        }

        // Vérification du statut de l'utilisateur
        if ($user->getStatut() === 0) {
            throw new AuthenticationException('Invalid credentials. Please try again. If you are an organizer, your account is not yet validated.');
        }

        // Crée un Passport avec l'utilisateur et le mot de passe
        return new Passport(
            new UserBadge($email),
            new PasswordCredentials($password)
        );
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        // Retrieve the authenticated user
        $user = $token->getUser();

        // Get the ID of the authenticated user
        $userId = $user->getUserId(); // Assuming 'getUserId()' returns the ID of the user

        // Log the user's ID or use it for any logic you need
        // You can add this line for debugging or tracking purposes
        // error_log('Authenticated User ID: ' . $userId);

        // Redirection après une connexion réussie en fonction du rôle
        if ($this->security->isGranted('ROLE_ADMIN')) {
            return new RedirectResponse('/user/app_users_index');
        } elseif ($this->security->isGranted('ROLE_ORGANISATEUR')) {
            return new RedirectResponse('/ProfileOrganisateur');
        } else {
            return new RedirectResponse('/homeProfile');
        }
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        // Gérer dynamiquement le message d'erreur en fonction du type d'exception
        $message = $exception->getMessageKey();

        if ($message === 'Invalid credentials. Please try again. If you are an organizer, your account is not yet validated.') {
            $request->getSession()->getFlashBag()->add('error', 'Invalid credentials. Please try again. If you are an organizer, your account is not yet validated..');
        } else {
            $request->getSession()->getFlashBag()->add('error', 'Invalid credentials. Please try again. If you are an organizer, your account is not yet validated.');
        }

        // Redirection vers la page de connexion avec message d'erreur
        return new RedirectResponse('/login');
    }
}
?>