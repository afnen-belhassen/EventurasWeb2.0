<?php

namespace App\Controller;

use App\Repository\ReclamationRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ReclamationsBackController extends AbstractController
{
    private $reclamationRepository;

    public function __construct(ReclamationRepository $reclamationRepository)
    {
        $this->reclamationRepository = $reclamationRepository;
    }

    #[Route('/reclamsBack', name: 'app_reclamations_back')]
    public function index(): Response
    {
        return $this->render('reclamation/index.html.twig');
    }

    #[Route('/reclamsBack/statistics', name: 'app_reclamations_statistics')]
    public function statistics(): Response
    {
        // Test data for demonstration
        $totalReclamations = 150;
        $pendingReclamations = 45;
        $resolvedReclamations = 85;
        $rejectedReclamations = 20;
        
        // Test data for monthly statistics
        $monthlyStats = [
            'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'],
            'data' => [12, 19, 15, 25, 22, 30]
        ];
        
        // Test data for type distribution
        $typeDistribution = [
            'labels' => ['Technical', 'Billing', 'Service', 'Other'],
            'data' => [40, 35, 25, 20]
        ];
        
        // Test data for status distribution
        $statusDistribution = [
            'Pending' => $pendingReclamations,
            'Resolved' => $resolvedReclamations,
            'Rejected' => $rejectedReclamations
        ];

        return $this->render('reclamation/statistics.html.twig', [
            'totalReclamations' => $totalReclamations,
            'pendingReclamations' => $pendingReclamations,
            'resolvedReclamations' => $resolvedReclamations,
            'avgResponseTime' => 2.5, // Test average response time in days
            'monthlyStats' => $monthlyStats,
            'typeDistribution' => $typeDistribution,
            'statusDistribution' => $statusDistribution
        ]);
    }
} 