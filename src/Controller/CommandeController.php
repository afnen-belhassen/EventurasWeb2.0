<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class CommandeController extends AbstractController
{
    private $entityManager;
    private $commandeRepository;
    private $produitRepository;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->produitRepository = $produitRepository;
        $this->logger = $logger;
    }

    #[Route('/commande/new/{produit_id}', name: 'ajouter_commande')]
    public function ajouterCommande(Request $request, int $produit_id = null): Response
    {
        $commande = new Commande();
        $form = $this->createForm(CommandeType::class, $commande);

        // Si un produit_id est fourni, pré-remplir le formulaire
        if ($produit_id) {
            $produit = $this->produitRepository->find($produit_id);
            if ($produit) {
                $commande->setProduit($produit);
                $form->setData($commande);
            }
        }

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Vérifier la disponibilité du stock
                $produit = $commande->getProduit();
                if ($produit->getQuantite() < $commande->getQuantite()) {
                    $this->addFlash('error', 'La quantité demandée n\'est pas disponible en stock.');
                    return $this->render('commande_form.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }

                // Mettre à jour le stock
                $produit->setQuantite($produit->getQuantite() - $commande->getQuantite());

                // Sauvegarder la commande
                $this->entityManager->persist($commande);
                $this->entityManager->flush();

                $this->logger->info('Commande créée avec succès', [
                    'commande_id' => $commande->getId(),
                    'client' => $commande->getNomClient(),
                    'produit' => $produit->getNom(),
                    'quantite' => $commande->getQuantite()
                ]);

                $this->addFlash('success', 'Commande passée avec succès!');
                return $this->redirectToRoute('app_boutique');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la création de la commande', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Une erreur est survenue lors de la création de la commande: ' . $e->getMessage());
            }
        }

        return $this->render('commande_form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/commandes', name: 'liste_commandes')]
    public function listeCommandes(): Response
    {
        $commandes = $this->commandeRepository->createQueryBuilder('c')
            ->leftJoin('c.produit', 'p')
            ->addSelect('p')
            ->orderBy('c.dateCommande', 'DESC')
            ->getQuery()
            ->getResult();
        
        return $this->render('liste_commandes.html.twig', [
            'commandes' => $commandes,
        ]);
    }

    #[Route('/commande/{id}/delete', name: 'supprimer_commande')]
    public function supprimerCommande(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);

        if (!$commande) {
            $this->addFlash('error', 'Commande non trouvée!');
            return $this->redirectToRoute('liste_commandes');
        }

        try {
            // Restaurer le stock
            $produit = $commande->getProduit();
            $produit->setQuantite($produit->getQuantite() + $commande->getQuantite());

            $this->commandeRepository->remove($commande, true);

            $this->addFlash('success', 'Commande supprimée avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression de la commande: ' . $e->getMessage());
        }

        return $this->redirectToRoute('liste_commandes');
    }
} 