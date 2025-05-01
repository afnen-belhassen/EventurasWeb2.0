<?php

namespace App\Controller\Admin;

use App\Repository\PartnerRepository;
use App\Repository\PartnershipRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/statistics')]
class StatisticsController extends AbstractController
{
    private $partnerRepository;
    private $partnershipRepository;

    public function __construct(
        PartnerRepository $partnerRepository,
        PartnershipRepository $partnershipRepository
    ) {
        $this->partnerRepository = $partnerRepository;
        $this->partnershipRepository = $partnershipRepository;
    }

    #[Route('/', name: 'app_admin_statistics')]
    public function index(): Response
    {
        // Basic statistics
        $totalPartners = $this->partnerRepository->count([]);
        $totalPartnerships = $this->partnershipRepository->count([]);
        $signedPartnerships = $this->partnershipRepository->count(['isSigned' => true]);

        // Partnership status distribution
        $partnershipStatus = [
            'pending' => $this->partnershipRepository->count(['status' => 'pending']),
            'signed' => $this->partnershipRepository->count(['status' => 'signed']),
            'rejected' => $this->partnershipRepository->count(['status' => 'rejected'])
        ];

        // Top performing partners
        $topPartners = $this->partnerRepository->findBy([], ['rating' => 'DESC'], 5);

        return $this->render('admin/statistics/index.html.twig', [
            'totalPartners' => $totalPartners,
            'totalPartnerships' => $totalPartnerships,
            'signedPartnerships' => $signedPartnerships,
            'partnershipStatus' => $partnershipStatus,
            'topPartners' => $topPartners,
        ]);
    }
} 