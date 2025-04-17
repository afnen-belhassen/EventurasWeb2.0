<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ProduitRepository;
use App\Repository\CommandeRepository;
use Doctrine\ORM\EntityManagerInterface;

class DashboardController extends AbstractController
{
    private $entityManager;
    private $commandeRepository;
    private $produitRepository;

    public function __construct(
        EntityManagerInterface $entityManager,
        CommandeRepository $commandeRepository,
        ProduitRepository $produitRepository
    ) {
        $this->entityManager = $entityManager;
        $this->commandeRepository = $commandeRepository;
        $this->produitRepository = $produitRepository;
    }

    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(): Response
    {
        // Récupérer toutes les commandes avec leurs produits
        $commandes = $this->commandeRepository->createQueryBuilder('c')
            ->leftJoin('c.produit', 'p')
            ->addSelect('p')
            ->getQuery()
            ->getResult();

        // Récupérer tous les produits
        $produits = $this->produitRepository->findAll();

        // Calculer le chiffre d'affaires total
        $chiffre_affaires = 0;
        foreach ($commandes as $commande) {
            $chiffre_affaires += $commande->getMontantTotal();
        }

        // Préparer les données pour le graphique des ventes par mois
        $ventesParMois = [];
        $labels = [];
        $currentYear = date('Y');
        
        for ($i = 1; $i <= 12; $i++) {
            $month = str_pad($i, 2, '0', STR_PAD_LEFT);
            $startDate = new \DateTime("$currentYear-$month-01");
            $endDate = new \DateTime("$currentYear-$month-31");
            
            $query = $this->entityManager->createQuery('
                SELECT SUM(c.quantite * p.prix) as total
                FROM App\Entity\Commande c
                JOIN c.produit p
                WHERE c.dateCommande BETWEEN :start AND :end
            ')
            ->setParameter('start', $startDate)
            ->setParameter('end', $endDate);
            
            $result = $query->getSingleScalarResult();
            $ventesParMois[] = $result ?: 0;
            $labels[] = date('M', strtotime("$currentYear-$month-01"));
        }

        // Préparer les données pour le graphique des top produits
        $query = $this->entityManager->createQuery('
            SELECT p.nom as nom, SUM(c.quantite) as total
            FROM App\Entity\Commande c
            JOIN c.produit p
            GROUP BY p.id
            ORDER BY total DESC
        ')
        ->setMaxResults(5);

        $topProduits = $query->getResult();
        $topProduitsLabels = array_column($topProduits, 'nom');
        $topProduitsData = array_column($topProduits, 'total');

        return $this->render('dashboard/index.html.twig', [
            'produits' => $produits,
            'commandes' => $commandes,
            'chiffre_affaires' => $chiffre_affaires,
            'ventesParMois' => json_encode($ventesParMois),
            'labels' => json_encode($labels),
            'topProduitsLabels' => json_encode($topProduitsLabels),
            'topProduitsData' => json_encode($topProduitsData),
        ]);
    }
} 