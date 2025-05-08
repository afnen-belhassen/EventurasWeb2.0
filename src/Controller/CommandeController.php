<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Entity\User;
use App\Entity\Users;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session;
use Stripe\PaymentIntent;
use Stripe\Stripe;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

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
            $commande->setDateCommande(new \DateTime());

            $produit->setQuantite($produit->getQuantite() - $quantite);

            $user = $this->security->getUser();
            if ($user instanceof Users) {
                $commande->setEmail($user->getEmail());
            }

            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            // Redirection directe vers le paiement avec l'ID de la commande
            return $this->redirectToRoute('commande_payment', ['id' => $commande->getId()]);
        }

        return $this->render('Commande/form.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }

    #[Route('/commande/payment/{id}', name: 'commande_payment')]
public function startPaiement(Request $request, EntityManagerInterface $em, UrlGeneratorInterface $urlGenerator, int $id): Response
{
    $commande = $em->getRepository(Commande::class)->find($id);

    if (!$commande) {
        throw $this->createNotFoundException("Commande introuvable.");
    }

    // Récupérer les données du formulaire
    $nomClient = $request->request->get('nom_client');
    $adresse = $request->request->get('adresse');
    $telephone = $request->request->get('telephone');
    $produit = $commande->getProduit(); // Récupérer le produit directement de la commande
    $quantite = $commande->getQuantite(); // Récupérer la quantité de la commande

    $total = $produit->getPrix() * $quantite;

    //**********Stripe Configuration**********//
    $session = Session::create([
        'payment_method_types' => ['card'],
        'line_items' => [[
            'price_data' => [
                'currency' => 'eur',
                'product_data' => [
                    'name' => $produit->getNom(),
                ],
                'unit_amount' => $produit->getPrix() * 100, // en centimes
            ],
            'quantity' => $quantite,
        ]],
        'mode' => 'payment',
        'success_url' => $urlGenerator->generate('paiement_success', ['id' => $id], UrlGeneratorInterface::ABSOLUTE_URL),
        'cancel_url' => $urlGenerator->generate('paiement_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL),
    ]);

    // Rediriger vers la page Stripe
    return $this->redirect($session->url, 303);
}

    

#[Route('/paiement/success/{id}', name: 'paiement_success')]
public function success(int $id, EntityManagerInterface $em): Response
{
    $commande = $em->getRepository(Commande::class)->find($id);

    if (!$commande) {
        throw $this->createNotFoundException("Commande introuvable.");
    }

    return $this->render('success.html.twig', [
        'id' => $commande->getId(), 
    ]);
}


    #[Route('/paiement/cancel', name: 'paiement_cancel')]
    public function cancel(): Response
    {
        return $this->render('cancel.html.twig');
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

#[Route('/commande/configuration/{id}', name: 'app_confirm_order')]
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
