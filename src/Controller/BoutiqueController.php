<?php

namespace App\Controller;

use App\Repository\ProduitRepository;
use App\Entity\Commande;
use App\Form\CommandeType;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class BoutiqueController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private ProduitRepository $produitRepository;
    private $cacheManager;


    public function __construct(EntityManagerInterface $entityManager,ProduitRepository $produitRepository, CacheManager $cacheManager)
       
        // Injection des dépendances pour l'EntityManager et le ProduitRepository
    {
        $this->entityManager = $entityManager;
        $this->produitRepository = $produitRepository;
        $this->cacheManager = $cacheManager;
    }
    #[Route('/boutique', name: 'app_boutique')]

    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        // Utilisation du repository pour récupérer les produits
        $produitsQuery = $this->produitRepository->findAllQuery();

        // Pagination : définir le nombre de produits par page
        $produits = $paginator->paginate(
            $produitsQuery, // La requête ou la collection à paginer
            $request->query->getInt('page', 1), // Numéro de la page, 1 par défaut
            6 // Nombre d'éléments par page
        );

        return $this->render('boutique/index.html.twig', [
            'produits' => $produits,
        ]);
    }


    #[Route('/commander/{id}', name: 'commander_produit', methods: ['GET', 'POST'])]
    public function commanderProduit(int $id, ProduitRepository $produitRepository, Request $request): Response
    {
        $produit = $produitRepository->find($id);
        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $commande = new Commande();
        $commande->setProduit($produit);
        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande = $form->getData();
            $commande->setDateCommande(new \DateTimeImmutable());
            $total = $commande->getProduit()->getPrix() * $commande->getQuantite();
            $commande->setTotal($total);

            // Utiliser l'injection de Doctrine pour persister la commande
            $this->entityManager->persist($commande);
            $this->entityManager->flush();

            $this->addFlash('success', 'Commande passée avec succès!');
            return $this->redirectToRoute('app_boutique');
        }

        return $this->render('commande/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => false,  // Vous êtes dans le mode "commander"
        ]);
    }
    #[Route('/produit/{id}/resize', name: 'resize_image')]
    public function resizeImage(int $id): Response
    {
        $produit = $this->produitRepository->find($id);

        if (!$produit) {
            throw $this->createNotFoundException('Produit non trouvé.');
        }

        $image = 'uploads/produits/' . $produit->getImage();

        // Vérifier si le fichier image existe
        $fullImagePath = $this->getParameter('kernel.project_dir') . '/public/' . $image;
        if (!file_exists($fullImagePath)) {
            throw $this->createNotFoundException('L\'image n\'existe pas.');
        }

        // Forcer la génération du cache d'image
        // Si le cache n'est pas généré, générez-le d'abord
        try {
            $this->cacheManager->getBrowserPath($image, 'product_image');
        } catch (\Exception $e) {
            // Si une exception est lancée, cela signifie que le cache n'a pas été généré, donc on va le forcer
            $this->cacheManager->resolve($image, 'product_image');
        }

        // Maintenant, récupérez le cache de l'image
        $cachePath = $this->cacheManager->getBrowserPath($image, 'product_image');
        
        if (!$cachePath) {
            throw $this->createNotFoundException('L\'image redimensionnée n\'a pas pu être générée.');
        }

        return new Response(
            '<img src="' . $cachePath . '" alt="Image agrandie" />'
        );
    }
    
    
    

}
