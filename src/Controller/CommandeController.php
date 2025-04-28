<?php

// src/Controller/CommandeController.php
namespace App\Controller;

use App\Entity\Commande;
use App\Entity\User;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

class CommandeController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProduitRepository $produitRepository;
    private CommandeRepository $commandeRepository;
    private Security $security;


    public function __construct(ProduitRepository $produitRepository,EntityManagerInterface $entityManager, Security $security, CommandeRepository $commandeRepository)
    {
        $this->entityManager = $entityManager;
        $this->produitRepository = $produitRepository;
        $this->commandeRepository = $commandeRepository;
        $this->security = $security;
    }


    #[Route('/commande', name: 'liste_commandes')]
    public function index(CommandeRepository $commandeRepository, ProduitRepository $produitRepository): Response
    {
        $commandes = $commandeRepository->findAll();
    
        foreach ($commandes as $commande) {
            if (!$commande->getProduit()) {
                $this->addFlash('error', 'Certaines commandes n\'ont pas de produit associé.');
            } else {
                $produit = $produitRepository->find($commande->getProduit()->getId());
                if (!$produit) {
                    $this->addFlash('error', 'Le produit associé à la commande ' . $commande->getId() . ' n\'existe pas.');
                }
            }
        }
    
        return $this->render('Commande/liste.html.twig', [
            'commandes' => $commandes,
        ]);
    }
    


    #[Route('/commande/ajouter/{id}', name: 'ajouter_commande', requirements: ['produit_id' => '\d+'])]
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
    
        if ($id !== 0) {
            $produit = $this->produitRepository->find($id);
            if (!$produit) {
                throw $this->createNotFoundException("Produit introuvable.");
            }
            $commande->setProduit($produit);
        }
    
        if ($form->isSubmitted() && $form->isValid()) {
            $produit = $commande->getProduit();
            $quantite = $commande->getQuantite();
    
            $commande->setTotal($produit->getPrix() * $quantite);
            $produit->setQuantite($produit->getQuantite() - $quantite);
    
    
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
public function edit(Request $request, Commande $commande, EntityManagerInterface $em): Response
{
    $form = $this->createForm(CommandeType::class, $commande);
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        $commande->setTotal($commande->getQuantite() * $commande->getProduit()?->getPrix() ?? 0);
        $em->flush();

        return $this->redirectToRoute('liste_commandes');
    }

    return $this->render('Commande/form.html.twig', [
        'form' => $form->createView(),
        'commande' => $commande,
    ]);
}
#[Route('/commande/supprimer/{id}', name: 'supprimer_commande')]
public function delete(Commande $commande, EntityManagerInterface $em): Response
{
    $em->remove($commande);
    $em->flush();

    return $this->redirectToRoute('liste_commandes');
}

#[Route('/commande/configuration/{id}', name: 'commande_configuration')]
public function orderConfirmation(int $id): Response
{
    $commande = $this->entityManager->getRepository(Commande::class)->find($id);

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
public function review(int $id)
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
    ]);
}

public function someAction(): Response
{
    // Utilisation de getUser
    $user = $this->security->getUser();

    if ($user instanceof User) {
        // L'utilisateur est authentifié, vous pouvez faire ce que vous voulez ici
        return new Response('User logged in: ' . $user->getEmail());
    } else {
        // L'utilisateur n'est pas authentifié
        return new Response('No user logged in');
    }
}

}

    
