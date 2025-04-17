<?php
namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;

#[Route('/produit')]
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

    #[Route('/new', name: 'ajouter_produit')]
    public function ajouterProduit(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->info('Form submitted', [
                'isValid' => $form->isValid(),
                'errors' => (string) $form->getErrors(true, false)
            ]);

            if ($form->isValid()) {
                try {
                    $this->logger->info('Attempting to persist product', [
                        'nom' => $produit->getNom(),
                        'prix' => $produit->getPrix()
                    ]);

                    $this->entityManager->persist($produit);
                    $this->entityManager->flush();

                    $this->logger->info('Product successfully persisted', [
                        'id' => $produit->getId()
                    ]);

                    $this->addFlash('success', 'Produit ajouté avec succès!');
                    return $this->redirectToRoute('liste_produits');
                } catch (\Exception $e) {
                    $this->logger->error('Error persisting product', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $this->addFlash('error', 'Une erreur est survenue lors de l\'ajout du produit: ' . $e->getMessage());
                }
            } else {
                $this->logger->error('Form validation failed', [
                    'errors' => (string) $form->getErrors(true, false)
                ]);
            }
        }

        return $this->render('ProduitForm.html.twig', [
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

    #[Route('/{id}/edit', name: 'modifier_produit')]
    public function modifierProduit(Request $request, int $id): Response
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé!');
            return $this->redirectToRoute('liste_produits');
        }

        $form = $this->createForm(ProduitType::class, $produit, [
            'edit_mode' => true
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted()) {
            $this->logger->info('Form submitted for modification', [
                'isValid' => $form->isValid(),
                'errors' => (string) $form->getErrors(true, false)
            ]);

            if ($form->isValid()) {
                try {
                    $this->logger->info('Attempting to update product', [
                        'id' => $produit->getId(),
                        'nom' => $produit->getNom(),
                        'prix' => $produit->getPrix()
                    ]);

                    $this->entityManager->flush();

                    $this->logger->info('Product successfully updated', [
                        'id' => $produit->getId()
                    ]);

                    $this->addFlash('success', 'Produit modifié avec succès!');
                    return $this->redirectToRoute('liste_produits');
                } catch (\Exception $e) {
                    $this->logger->error('Error updating product', [
                        'error' => $e->getMessage(),
                        'trace' => $e->getTraceAsString()
                    ]);
                    $this->addFlash('error', 'Une erreur est survenue lors de la modification du produit: ' . $e->getMessage());
                }
            } else {
                $this->logger->error('Form validation failed', [
                    'errors' => (string) $form->getErrors(true, false)
                ]);
            }
        }

        return $this->render('ProduitForm.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit
        ]);
    }

    #[Route('/{id}/delete', name: 'supprimer_produit')]
    public function supprimerProduit(int $id): Response
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            $this->addFlash('error', 'Produit non trouvé!');
            return $this->redirectToRoute('liste_produits');
        }

        try {
            $this->entityManager->remove($produit);
            $this->entityManager->flush();
            $this->addFlash('success', 'Produit supprimé avec succès!');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Une erreur est survenue lors de la suppression du produit: ' . $e->getMessage());
        }

        return $this->redirectToRoute('liste_produits');
    }

    #[Route('/form', name: 'app_produit_form')]
    public function index(Request $request, EntityManagerInterface $entityManager): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($produit);
            $entityManager->flush();

            $this->addFlash('success', 'Produit ajouté avec succès!');
            return $this->redirectToRoute('liste_produits');
        }

        return $this->render('produit/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false
        ]);
    }

    #[Route('/{id}/edit', name: 'modifier_produit')]
    public function edit(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            $this->addFlash('success', 'Produit modifié avec succès!');
            return $this->redirectToRoute('liste_produits');
        }

        return $this->render('produit/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true
        ]);
    }

    #[Route('/produits', name: 'liste_produits')]
    public function list(ProduitRepository $produitRepository): Response
    {
        $produits = $produitRepository->findAll();
        return $this->render('produit/list.html.twig', [
            'produits' => $produits
        ]);
    }

    #[Route('/{id}/delete', name: 'supprimer_produit')]
    public function delete(Produit $produit, EntityManagerInterface $entityManager): Response
    {
        $entityManager->remove($produit);
        $entityManager->flush();

        $this->addFlash('success', 'Produit supprimé avec succès!');
        return $this->redirectToRoute('liste_produits');
    }
}
