<?php

namespace App\Controller;

use App\Entity\Partner;
use App\Entity\Partnership;
use App\Form\PartnershipType;
use App\Form\SignedContractType;
use App\Repository\PartnerRepository;
use App\Repository\PartnershipRepository;
use App\Service\ContractGeneratorService;
use App\Service\SignatureVerificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\Form\Extension\Core\Type\FileType;
use Symfony\Component\Validator\Constraints\File;

#[Route('/organizer')]
class OrganizerController extends AbstractController
{
    private $contractGenerator;
    private $signatureVerificationService;
    private $entityManager;
    private $logger;
    
    public function __construct(
        ContractGeneratorService $contractGenerator,
        SignatureVerificationService $signatureVerificationService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger
    ) {
        $this->contractGenerator = $contractGenerator;
        $this->signatureVerificationService = $signatureVerificationService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
    }
    
    #[Route('/', name: 'app_organizer_index', methods: ['GET'])]
    public function index(PartnerRepository $partnerRepository): Response
    {
        return $this->render('organizer/index.html.twig', [
            'partners' => $partnerRepository->findAll(),
        ]);
    }

    #[Route('/partner/{id}', name: 'app_organizer_partner_show', methods: ['GET'])]
    public function show(Partner $partner): Response
    {
        return $this->render('organizer/show.html.twig', [
            'partner' => $partner,
        ]);
    }

    #[Route('/partner/{id}/create-partnership', name: 'app_organizer_create_partnership', methods: ['GET', 'POST'])]
    public function createPartnership(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        $partnership = new Partnership();
        $partnership->setPartnerId($partner);
        $partnership->setOrganizerid(1);
        $partnership->setIsSigned(false);
        $partnership->setStatus('pending');
        $partnership->setCreatedAt(new \DateTime());
        
        $form = $this->createForm(PartnershipType::class, $partnership);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partnership);
            $entityManager->flush();
            
            // Generate PDF contract
            $contractFilename = $this->contractGenerator->generateContract($partnership, $partner);
            
            $this->addFlash('success', 'Partnership created successfully! A contract has been generated for your review.');
            
            return $this->redirectToRoute('contract_management', [
                'id' => $partnership->getId()
            ]);
        }
        
        return $this->render('organizer/create_partnership.html.twig', [
            'partner' => $partner,
            'partnership' => $partnership,
            'form' => $form->createView(),
        ]);
    }
    
    #[Route('/partnership/{id}/contract', name: 'app_organizer_view_contract', methods: ['GET'])]
    public function viewContract(Partnership $partnership, Request $request): Response
    {
        $contractFilename = $request->query->get('contract');
        
        if (!$contractFilename) {
            $this->addFlash('error', 'Contract file not found.');
            return $this->redirectToRoute('app_organizer_index');
        }
        
        // Check if this is a signed contract
        if (strpos($contractFilename, 'signed/') === 0) {
            $contractPath = 'uploads/signed_contracts/' . substr($contractFilename, 7);
        } else {
            $contractPath = 'contracts/' . $contractFilename;
        }
        
        return $this->render('organizer/view_contract.html.twig', [
            'partnership' => $partnership,
            'contractPath' => $contractPath,
        ]);
    }
    
    #[Route('/partnership/{id}/contract-management', name: 'contract_management')]
    public function contractManagement(Request $request, Partnership $partnership, SignatureVerificationService $signatureVerificationService): Response
    {
        $this->logger->info('Contract management for partnership ID: ' . $partnership->getId());
        
        $signedContractPath = null;
        $contractPath = null;

        // Create form for contract upload
        $form = $this->createForm(SignedContractType::class);
        $form->handleRequest($request);

        // Check if there's a signed contract
        if ($partnership->getSignedContractFile()) {
            $signedContractPath = 'uploads/organizer/signed_contracts/' . $partnership->getSignedContractFile();
            $this->logger->info('Found signed contract: ' . $signedContractPath);
        }

        // Find the original contract file
        $contractsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/contracts/';
        $files = glob($contractsDir . 'contract_' . $partnership->getId() . '_*.pdf');
        if (!empty($files)) {
            $contractPath = 'uploads/organizer/contracts/' . basename($files[0]);
            $this->logger->info('Found original contract: ' . $contractPath);
        } else {
            $this->logger->warning('No original contract found for partnership ID: ' . $partnership->getId());
        }

        // Handle contract upload
        if ($form->isSubmitted() && $form->isValid()) {
            $this->logger->info('Contract upload form submitted');
            
            $file = $form->get('contractFile')->getData();
            if ($file) {
                $this->logger->info('Processing uploaded contract file: ' . $file->getClientOriginalName());
                
                try {
                    // Verify the signature
                    if ($signatureVerificationService->verifySignature($file->getPathname())) {
                        // Save the signed contract
                        $filename = $signatureVerificationService->saveContract($file, $partnership->getId());
                        $partnership->setSignedContractFile($filename);
                        $partnership->setStatus('signed');
                        $partnership->setIsSigned(true);
                        $partnership->setSignedAt(new \DateTime());
                        $this->entityManager->flush();
                        
                        $this->addFlash('success', 'Contract signed successfully!');
                        return $this->redirectToRoute('contract_management', ['id' => $partnership->getId()]);
                    } else {
                        $this->logger->warning('Contract verification failed for file: ' . $file->getClientOriginalName());
                        $this->addFlash('error', 'Invalid contract file. Please upload a valid signed PDF contract.');
                    }
                } catch (\Exception $e) {
                    $this->logger->error('Error processing contract: ' . $e->getMessage());
                    $this->addFlash('error', 'Error processing contract: ' . $e->getMessage());
                }
            }
        }

        return $this->render('organizer/contract_management.html.twig', [
            'partnership' => $partnership,
            'form' => $form->createView(),
            'signedContractPath' => $signedContractPath,
            'contractPath' => $contractPath,
        ]);
    }
    
    #[Route('/contract/{type}/{filename}', name: 'app_organizer_contract_file', methods: ['GET'])]
    public function serveContractFile(string $type, string $filename): Response
    {
        $this->logger->info('Serving contract file: ' . $filename . ' of type: ' . $type);
        
        $basePath = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/';
        $filePath = $basePath . $type . '/' . $filename;

        if (!file_exists($filePath)) {
            $this->logger->error('Contract file not found: ' . $filePath);
            throw $this->createNotFoundException('Contract file not found');
        }

        $this->logger->info('Serving contract file from: ' . $filePath);
        
        $response = new BinaryFileResponse($filePath);
        $response->headers->set('Content-Type', 'application/pdf');
        $response->headers->set('Content-Disposition', 'inline; filename="' . $filename . '"');

        return $response;
    }

    #[Route('/partners', name: 'app_organizer_partners', methods: ['GET'])]
    public function listPartners(PartnerRepository $partnerRepository): Response
    {
        return $this->render('organizer/partners.html.twig', [
            'partners' => $partnerRepository->findAll(),
        ]);
    }

    #[Route('/partner/{id}/view', name: 'app_organizer_partner_view', methods: ['GET'])]
    public function viewPartner(Partner $partner, PartnershipRepository $partnershipRepository): Response
    {
        $partnerships = $partnershipRepository->findBy(['partnerId' => $partner->getId()], ['createdAt' => 'DESC']);
        
        return $this->render('organizer/partner_view.html.twig', [
            'partner' => $partner,
            'partnerships' => $partnerships,
        ]);
    }

    #[Route('/partner/{id}/generate-partnership', name: 'app_organizer_generate_partnership', methods: ['GET', 'POST'])]
    public function generatePartnership(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        if ($request->isMethod('POST')) {
            // Create a new partnership with all required fields
            $partnership = new Partnership();
            $partnership->setPartnerId($partner);
            $partnership->setName('Partnership with ' . $partner->getName());
            $partnership->setStatus('pending');
            $partnership->setCreatedAt(new \DateTime());
            $partnership->setContracttype('Standard');
            $partnership->setDescription('Partnership with ' . $partner->getName());
            $partnership->setOrganizerid(1); // Hardcoded for testing
            
            // Set optional fields from form data
            $partnership->setEmail($request->request->get('email'));
            $partnership->setPhone($request->request->get('phone'));
            $partnership->setAddress($request->request->get('address'));
            
            // Persist and flush
            $entityManager->persist($partnership);
            $entityManager->flush();
            
            $this->addFlash('success', 'Partnership request has been created successfully.');
            return $this->redirectToRoute('app_organizer_partners');
        }
        
        // For GET requests, just render the form
        return $this->render('organizer/generate_partnership.html.twig', [
            'partner' => $partner,
        ]);
    }

    #[Route('/partnerships', name: 'app_organizer_partnerships', methods: ['GET'])]
    public function partnerships(PartnershipRepository $partnershipRepository): Response
    {
        $this->logger->info('Accessing partnerships list');
        
        return $this->render('organizer/partnerships.html.twig', [
            'partnerships' => $partnershipRepository->findAll(),
        ]);
    }
    
    #[Route('/partnership/{id}/sign', name: 'app_organizer_sign_contract', methods: ['GET'])]
    public function signContract(Partnership $partnership): Response
    {
        // Find the original contract file
        $contractsDir = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/contracts/';
        $files = glob($contractsDir . 'contract_' . $partnership->getId() . '_*.pdf');
        
        if (empty($files)) {
            $this->addFlash('error', 'Contract file not found.');
            return $this->redirectToRoute('contract_management', ['id' => $partnership->getId()]);
        }
        
        $contractFilename = basename($files[0]);
        
        return $this->render('organizer/sign_online.html.twig', [
            'partnership' => $partnership,
            'contractFilename' => $contractFilename,
        ]);
    }

    #[Route('/partnership/{id}/upload-signed-contract', name: 'app_organizer_upload_signed_contract')]
    public function uploadSignedContract(Request $request, Partnership $partnership, SignatureVerificationService $signatureVerificationService): Response
    {
        $form = $this->createForm(SignedContractType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $file = $form->get('signedContract')->getData();
            
            try {
                // Verify the signature
                if ($signatureVerificationService->verifySignature($file->getPathname())) {
                    // Save the signed contract
                    $filename = $signatureVerificationService->saveContract($file, $partnership->getId());
                    $partnership->setSignedContractFile($filename);
                    $partnership->setStatus('signed');
                    $partnership->setIsSigned(true);
                    $partnership->setSignedAt(new \DateTime());
                    $this->entityManager->flush();

                    $this->addFlash('success', 'Le contrat signé a été téléversé avec succès et vérifié. Le statut du partenariat a été mis à jour.');
                    return $this->redirectToRoute('contract_management', ['id' => $partnership->getId()]);
                } else {
                    $this->addFlash('error', 'Le fichier du contrat n\'est pas valide ou n\'est pas correctement signé. Veuillez téléverser un PDF valide avec une signature.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Une erreur est survenue lors du téléversement du contrat: ' . $e->getMessage());
            }
        }

        // Afficher la page dédiée au téléchargement et à la vérification
        return $this->render('organizer/upload_signed_contract.html.twig', [
            'partnership' => $partnership,
            'form' => $form->createView(),
        ]);
    }
} 