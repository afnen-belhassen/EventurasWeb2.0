<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProduitRepository $produitRepository;
    private CommandeRepository $commandeRepository;
    private Security $security;
    private MailerInterface $mailer;

    public function __construct(
        ProduitRepository $produitRepository,
        EntityManagerInterface $entityManager,
        Security $security,
        CommandeRepository $commandeRepository,
        MailerInterface $mailer
    ) {
        $this->entityManager = $entityManager;
        $this->produitRepository = $produitRepository;
        $this->commandeRepository = $commandeRepository;
        $this->security = $security;
        $this->mailer = $mailer;
    }

    #[Route('/commande', name: 'liste_commandes')]
    public function show(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Aucune commande trouvée pour cet ID.');
        }

        $email = $commande->getEmail();

        return $this->render('commande/show.html.twig', [
            'commande' => $commande,
            'email' => $email,
        ]);
    }

    #[Route('/commande/ajouter/{id}', name: 'ajouter_commande', requirements: ['id' => '\d+'])]
    public function ajouterCommande(Request $request, int $id): Response
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $commande = new Commande();
        $commande->setProduit($produit);
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $quantite = $commande->getQuantite();
            $commande->setTotal($produit->getPrix() * $quantite);
            $produit->setQuantite($produit->getQuantite() - $quantite);

            $user = $this->security->getUser();
            if ($user instanceof User) {
                $commande->setEmail($user->getEmail());
            }

            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            $this->addFlash('success', 'Commande ajoutée avec succès !');

            return $this->redirectToRoute('commande_configuration', [
                'id' => $commande->getId(),
            ]);
        }

        return $this->render('Commande/form.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/commande/modifier/{id}', name: 'modifier_commande')]
    public function edit(Request $request, Commande $commande): Response
    {
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setTotal($commande->getQuantite() * $commande->getProduit()?->getPrix() ?? 0);
            $this->entityManager->flush();

            return $this->redirectToRoute('liste_commandes');
        }

        return $this->render('Commande/form.html.twig', [
            'form' => $form->createView(),
            'commande' => $commande,
        ]);
    }

    #[Route('/commande/supprimer/{id}', name: 'supprimer_commande')]
    public function delete(Commande $commande): Response
    {
        $this->entityManager->remove($commande);
        $this->entityManager->flush();

        return $this->redirectToRoute('liste_commandes');
    }

    #[Route('/commande/configuration/{id}', name: 'commande_configuration')]
    public function orderConfirmation(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        $user = $this->security->getUser();
        $customerEmail = ($user instanceof User) ? $user->getEmail() : null;

        return $this->render('order_confirmation.html.twig', [
            'commande' => $commande,
            'customerEmail' => $customerEmail,
        ]);
    }

    #[Route('/commande/{id}/review', name: 'commande_review')]
    public function review(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);
        $user = $this->security->getUser();
        $customerEmail = ($user instanceof User) ? $user->getEmail() : null;

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('review.html.twig', [
            'commande' => $commande,
            'customerEmail' => $customerEmail,
        ]);
    }

    #[Route('/commande/{id}/payment', name: 'commande_payment')]
    public function payment(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);
        $user = $this->security->getUser();
        $customerEmail = ($user instanceof User) ? $user->getEmail() : null;

        if (!$commande) {
            throw $this->createNotFoundException('Commande non trouvée');
        }

        return $this->render('payment.html.twig', [
            'commande' => $commande,
            'customerEmail' => $customerEmail,
            'stripe_public_key' => $_ENV['STRIPE_PUBLIC_KEY'],
        ]);
    }


    #[Route('/paiement/intent', name: 'app_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $id = $data['id'] ?? null;

        if (!$id) {
            return new JsonResponse(['error' => 'ID de commande manquant.'], 400);
        }

        $commande = $this->commandeRepository->find($id);
        if (!$commande) {
            return new JsonResponse(['error' => 'Commande introuvable.'], 404);
        }

        Stripe::setApiKey($_ENV['STRIPE_SECRET_KEY']);

        try {
            $paymentIntent = PaymentIntent::create([
                'amount' => (int) ($commande->getTotal() * 100),
                'currency' => 'eur',
            ]);

            return new JsonResponse(['clientSecret' => $paymentIntent->client_secret]);
        } catch (\Exception $e) {
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }

    #[Route('/test-mail', name: 'envoyer_test_mail')]
    public function testEmail(MailerInterface $mailer): Response
    {
        $email = (new Email())
            ->from('noreply@votresite.com')
            ->to('nassir.bouzaienne@esprit.tn')  // <-- Email fixe pour test
            ->subject('Test d\'envoi de mail depuis Symfony')
            ->html('<p>Ceci est un email de test envoyé depuis l\'application Symfony.</p>');
    
        try {
            $mailer->send($email);
            return new Response('Email envoyé avec succès.');
        } catch (\Exception $e) {
            return new Response('Erreur lors de l\'envoi : ' . $e->getMessage());
        }
    }
    
    

    

    #[Route('/confirm-order', name: 'app_confirm_order', methods: ['POST'])]
    public function confirmOrder(Request $request): Response
    {
        $id = $request->request->get('id');
        $paymentIntentId = $request->request->get('payment_intent_id');
        
        $commande = $this->commandeRepository->find($id);

        if ($commande) {
            $commande->setStripePaymentId($paymentIntentId);
            $this->entityManager->flush();
        }

        $this->addFlash('success', 'Commande confirmée et PaymentIntent enregistré.');

        return $this->redirectToRoute('app_payment_success', ['id' => $commande->getId()]);
    }
}
