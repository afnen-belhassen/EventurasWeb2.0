<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;

class ProduitController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private ProduitRepository $produitRepository,
        private LoggerInterface $logger
    ) {}

    #[Route('/ajouter', name: 'ajouter_produit')]
    public function ajouterProduit(Request $request): Response
    {
        $produit = new Produit();
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
    
            $pictureFile = $form->get('image')->getData();
            if ($pictureFile) {
                $newFilename = uniqid().'.'.$pictureFile->guessExtension();
                try {
                    // Use the correct upload directory path
                    $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
                    $pictureFile->move($uploadsDirectory, $newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                }
                // Set the image filename to the product
                $produit->setImage($newFilename);
            }
    
            $this->entityManager->persist($produit);
            $this->entityManager->flush();
    
            $this->addFlash('success', 'Produit ajouté avec succès');
            return $this->redirectToRoute('liste_produits');
        }
    
        return $this->render('Produit/form.html.twig', [
            'form' => $form->createView()
        ]);
    }
    

    #[Route('/produit', name: 'liste_produits')]
    public function listeProduits(): Response
    {
        $produit = $this->produitRepository->findAll();

        return $this->render('Produit/liste.html.twig', [
            'produits' => $produit
        ]);
    }

    #[Route('/{id}/modifier', name: 'modifier_produit')]
    public function modifierProduit(Request $request, int $id): Response
    {
        // Fetch the product from the database
        $produit = $this->produitRepository->find($id);
    
        // If product doesn't exist, throw a 404 error
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé');
        }
    
        // Create the form and handle the request
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload if a new image is provided
            $pictureFile = $form->get('image')->getData();
            if ($pictureFile) {
                // Define the uploads directory path
                $uploadsDirectory = $this->getParameter('kernel.project_dir') . '/public/uploads/produits';
    
                // Generate a unique filename
                $newFilename = uniqid() . '.' . $pictureFile->guessExtension();
                try {
                    // Move the uploaded file to the defined directory
                    $pictureFile->move($uploadsDirectory, $newFilename);
    
                    // Update the product's image property with the new filename
                    $produit->setImage($newFilename);
                } catch (FileException $e) {
                    // If there's an error, show an error message
                    $this->addFlash('error', 'Erreur lors de l’upload de l’image.');
                }
            }
    
            // Persist the changes to the database
            $this->entityManager->flush();
    
            // Display success message and redirect
            $this->addFlash('success', 'Produit mis à jour');
            return $this->redirectToRoute('liste_produits');
        }
    
        // Render the form view
        return $this->render('Produit/form.html.twig', [
            'form' => $form->createView(),
            'produit' => $produit,
        ]);
    }
    
    
    
    

    #[Route('/{id}/supprimer', name: 'supprimer_produit')]
    public function supprimerProduit(int $id): Response
    {
        $produit = $this->produitRepository->find($id);
    if (!$produit) {
        throw $this->createNotFoundException('Produit non trouvé');
    }


        if ($produit) {
            $this->entityManager->remove($produit);
            $this->entityManager->flush();
            $this->addFlash('success', 'Produit supprimé');
        } else {
            $this->addFlash('error', 'Produit introuvable');
        }

        return $this->redirectToRoute('liste_produits');
    }

    
 

}
