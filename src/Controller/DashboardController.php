<?php

namespace App\Controller;

use App\Entity\Commande;
use App\Entity\Produit;
use App\Form\CommandeType;
use App\Repository\CommandeRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    private EntityManagerInterface $entityManager;
    private CommandeRepository $commandeRepository;
    private ProduitRepository $produitRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->produitRepository = $produitRepository;
    }
    

    #[Route('/commande', name: 'liste_commandes')]
    public function listeCommandes(): Response
    {
        $commandes = $this->commandeRepository->findAll();

        return $this->render('Commande/liste.html.twig', [
            'commandes' => $commandes
        ]);
    }

    #[Route('/produit', name: 'liste_produits')]
    public function listeProduits(): Response
    {
        $produits = $this->produitRepository->findAll();

        return $this->render('Produit/liste.html.twig', [
            'produits' => $produits
        ]);
    }


    #[Route('/dashboard1', name: 'app_dashboard1')]
    public function index(): Response
    {
        $commandes = $this->commandeRepository->createQueryBuilder('c')
            ->leftJoin('c.produit', 'p')
            ->addSelect('p')
            ->orderBy('c.date_commande', 'DESC')
            ->getQuery()
            ->getResult();

        $produits = $this->produitRepository->findAll();

        $chiffre_affaires = 0;
        foreach ($commandes as $commande) {
            $chiffre_affaires += $commande->getTotal() ?: 0;
        }

        $ventesParMois = [];
        $labels = [];
        $currentYear = date('Y');

        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $startDate = new \DateTime("$currentYear-$month-01");
            $endDate = (clone $startDate)->modify('last day of this month');

            $result = $this->entityManager->createQuery('
                SELECT SUM(c.quantite * p.prix) as total
                FROM App\Entity\Commande c
                JOIN c.produit p
                WHERE c.date_commande BETWEEN :start AND :end
            ')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate)
            ->getSingleScalarResult();

            $ventesParMois[] = $result ?: 0;
            $labels[] = date('M', strtotime("$currentYear-$month-01"));
        }

        $topProduits = $this->entityManager->createQuery('
            SELECT p.nom as nom, SUM(c.quantite) as total
            FROM App\Entity\Commande c
            JOIN c.produit p
            GROUP BY p.id
            ORDER BY total DESC
        ')
        ->setMaxResults(5)
        ->getResult();

        $topProduitsLabels = array_column($topProduits, 'nom');
        $topProduitsData = array_column($topProduits, 'total');

        return $this->render('backOFF/nassiradmin.html.twig', [
            'produit' => $produits,
            'commandes' => $commandes,
            'chiffre_affaires' => $chiffre_affaires,
            'ventesParMois' => json_encode($ventesParMois),
            'labels' => json_encode($labels),
            'topProduitsLabels' => json_encode($topProduitsLabels),
            'topProduitsData' => json_encode($topProduitsData),
        ]);
    }

  
    #[Route('/commande/modifier/{id}', name: 'modifier_commande', methods: ['GET', 'POST'])]
    public function modifierCommande(Request $request, int $id): Response
    {
        $commande = $this->commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException("Commande introuvable.");
        }

        $form = $this->createForm(CommandeType::class, $commande);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $commande->setTotal($commande->getProduit()->getPrix() * $commande->getQuantite());
            $this->entityManager->flush();

            $this->addFlash('success', 'Commande modifiée avec succès !');
            return $this->redirectToRoute('liste_commandes');
        }

        return $this->render('Commande/form.html.twig', [
            'form' => $form->createView(),
            'is_edit' => true,
        ]);
    }

    #[Route('/commande/supprimer/{id}', name: 'supprimer_commande', methods: ['GET'])]
    public function supprimerCommande(int $id): Response
    {
        $commande = $this->commandeRepository->find($id);

        if (!$commande) {
            throw $this->createNotFoundException("Commande introuvable.");
        }

        $this->entityManager->remove($commande);
        $this->entityManager->flush();

        $this->addFlash('success', 'Commande supprimée avec succès.');
        return $this->redirectToRoute('liste_commandes');
    }
}
