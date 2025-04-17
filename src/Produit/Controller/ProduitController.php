<?php

namespace App\Produit\Controller;

use App\Produit\Entity\Produit;
use App\Produit\Form\ProduitType;
use App\Produit\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

class ProduitController extends AbstractController
{
    private $entityManager;
    private $produitRepository;
    private $logger;

    public function __construct(
        EntityManagerInterface $entityManager,
        ProduitRepository $produitRepository,
        LoggerInterface $logger
    ) {
        $this->entityManager = $entityManager;
        $this->produitRepository = $produitRepository;
        $this->logger = $logger;
    }

    #[Route('/produit/new', name: 'ajouter_produit')]
    public function ajouterProduit(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $this->entityManager->persist($produit);
                $this->entityManager->flush();

                $this->logger->info('Produit créé avec succès', [
                    'produit_id' => $produit->getId(),
                    'nom' => $produit->getNom()
                ]);

                $this->addFlash('success', 'Produit ajouté avec succès!');
                return $this->redirectToRoute('liste_produits');
            } catch (\Exception $e) {
                $this->logger->error('Erreur lors de la création du produit', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Une erreur est survenue lors de la création du produit: ' . $e->getMessage());
            }
        }

        return $this->render('produit/form.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/produits', name: 'liste_produits')]
    public function listeProduits(): Response
    {
        $produits = $this->produitRepository->findAll();
        
        return $this->render('liste_produits.html.twig', [
            'produits' => $produits,
        ]);
    }

    #[Route('/produit/{id}/delete', name: 'supprimer_produit')]
    public function supprimerProduit(int $id): Response
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé!');
            return $this->redirectToRoute('liste_produits');
        }

        try {
            $this->produitRepository->remove($produit, true);

            $this->addFlash('success', 'Produit supprimé avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du produit: ' . $e->getMessage());
        }

        return $this->redirectToRoute('liste_produits');
    }
} 