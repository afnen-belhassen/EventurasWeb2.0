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
use App\Service\NotificationService;

#[Route('/organizer')]
class OrganizerController extends AbstractController
{
    private $contractGenerator;
    private $signatureVerificationService;
    private $entityManager;
    private $logger;
    private $notificationService;
    
    public function __construct(
        ContractGeneratorService $contractGenerator,
        SignatureVerificationService $signatureVerificationService,
        EntityManagerInterface $entityManager,
        LoggerInterface $logger,
        NotificationService $notificationService
    ) {
        $this->contractGenerator = $contractGenerator;
        $this->signatureVerificationService = $signatureVerificationService;
        $this->entityManager = $entityManager;
        $this->logger = $logger;
        $this->notificationService = $notificationService;
    }
    
    #[Route('/', name: 'app_organizer_index', methods: ['GET'])]
    public function index(PartnerRepository $partnerRepository, Request $request): Response
    {
        $sortBy = $request->query->get('sort', 'name');
        $direction = $request->query->get('direction', 'asc');

        $partners = $partnerRepository->findAllSorted($sortBy, $direction);

        return $this->render('organizer/index.html.twig', [
            'partners' => $partners,
            'sortBy' => $sortBy,
            'direction' => $direction,
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
            
            return $this->redirectToRoute('app_organizer_contract_management', [
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
    
    #[Route('/partnership/{id}/contract-management', name: 'app_organizer_contract_management', methods: ['GET', 'POST'])]
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
            $signedContractPath = $partnership->getSignedContractFile();
            $this->logger->info('Found signed contract: ' . $signedContractPath);
        }

        // Find or generate the original contract file
        $basePath = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/';
        $contractsDir = $basePath . 'contracts/';
        
        if (!file_exists($contractsDir)) {
            mkdir($contractsDir, 0777, true);
        }
        
        // First try to find by partnership ID
        $files = glob($contractsDir . 'contract_' . $partnership->getId() . '_*.pdf');
        if (empty($files)) {
            // If not found, generate a new contract
            $this->logger->info('No existing contract found, generating new one');
            $contractFilename = $this->contractGenerator->generateContract($partnership, $partnership->getPartnerId());
            
            if ($contractFilename) {
                // Move the generated contract to the correct directory
                $sourcePath = $basePath . $contractFilename;
                $targetPath = $contractsDir . basename($contractFilename);
                
                if (file_exists($sourcePath)) {
                    if (!file_exists($contractsDir)) {
                        mkdir($contractsDir, 0777, true);
                    }
                    rename($sourcePath, $targetPath);
                    $contractPath = basename($contractFilename);
                    $this->logger->info('Generated and moved new contract to: ' . $targetPath);
                } else {
                    $this->logger->error('Generated contract not found at source path: ' . $sourcePath);
                    $this->addFlash('error', 'Erreur lors de la génération du contrat. Veuillez réessayer.');
                }
            }
        } else {
            $contractPath = basename($files[0]);
            $this->logger->info('Found existing contract: ' . $contractPath);
        }

        // If we still don't have a contract path, try to generate one more time
        if (!$contractPath) {
            $this->logger->warning('No contract path available, attempting to generate one more time');
            $contractFilename = $this->contractGenerator->generateContract($partnership, $partnership->getPartnerId());
            if ($contractFilename) {
                $contractPath = basename($contractFilename);
                $this->logger->info('Successfully generated contract: ' . $contractPath);
            }
        }

        $this->logger->info('Final contract path: ' . $contractPath);
        $this->logger->info('Final signed contract path: ' . $signedContractPath);

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
        
        // Ensure the filename is properly decoded
        $filename = urldecode($filename);
        
        // Get the base path for uploads
        $basePath = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/';
        
        // Construct the full file path
        $filePath = $basePath . $type . '/' . $filename;
        
        $this->logger->info('Looking for contract file at: ' . $filePath);
        $this->logger->info('Base path: ' . $basePath);
        $this->logger->info('Type: ' . $type);
        $this->logger->info('Filename: ' . $filename);

        if (!file_exists($filePath)) {
            $this->logger->error('Contract file not found at: ' . $filePath);
            
            // Try alternative paths for signed contracts
            if ($type === 'signed_contracts') {
                $altPath = $basePath . 'signed_contracts/' . $filename;
                $this->logger->info('Trying alternative path for signed contract: ' . $altPath);
                if (file_exists($altPath)) {
                    $filePath = $altPath;
                }
            }
            
            if (!file_exists($filePath)) {
                throw $this->createNotFoundException('Contract file not found');
            }
        }

        $this->logger->info('Found contract file at: ' . $filePath);
        
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



    #[Route('/partnerships', name: 'app_organizer_partnerships', methods: ['GET'])]
    public function partnerships(PartnershipRepository $partnershipRepository): Response
    {
        return $this->render('organizer/partnerships.html.twig', [
            'partnerships' => $partnershipRepository->findAll(),
        ]);
    }
    
    #[Route('/partner/{partnerId}/rate', name: 'app_organizer_rate_partner', methods: ['POST'])]
    public function ratePartner(Request $request, int $partnerId, EntityManagerInterface $entityManager): JsonResponse
    {
        $rating = $request->request->get('rating');
        
        if (!is_numeric($rating) || $rating < 0 || $rating > 5) {
            return $this->json([
                'success' => false,
                'message' => 'Invalid rating value'
            ]);
        }
        
        try {
            // Get the partner from the database
            $partner = $entityManager->getRepository(Partner::class)->find($partnerId);
            
            if (!$partner) {
                return $this->json([
                    'success' => false,
                    'message' => 'Partner not found'
                ]);
            }
            
            // Get current rating and count
            $currentRating = $partner->getRating() ?? 0;
            $currentCount = $partner->getRatingCount() ?? 0;
            
            // Calculate new average
            $newCount = $currentCount + 1;
            $newRating = (($currentRating * $currentCount) + $rating) / $newCount;
            
            // Update partner
            $partner->setRating($newRating);
            $partner->setRatingCount($newCount);
            
            // Save to database
            $entityManager->flush();
            
            // Get the total rating that includes the partnership bonus
            $totalRating = $partner->getTotalRating();
            
            return $this->json([
                'success' => true,
                'newRating' => round($totalRating, 1),
                'baseRating' => round($newRating, 1),
                'newCount' => $newCount
            ]);
        } catch (\Exception $e) {
            return $this->json([
                'success' => false,
                'message' => 'Error saving rating: ' . $e->getMessage()
            ]);
        }
    }

    #[Route('/partnership/{id}/upload-signed-contract', name: 'app_organizer_upload_signed_contract', methods: ['POST'])]
    public function uploadSignedContract(Request $request, Partnership $partnership, EntityManagerInterface $entityManager): Response
    {
        // Debugging: Log request data
        $this->logger->debug('Upload attempt', [
            'files' => $request->files->all(),
            'post' => $request->request->all()
        ]);

        if ($partnership->isSigned()) {
            $this->addFlash('error', 'This contract is already signed.');
            return $this->redirectToRoute('app_organizer_contract_management', ['id' => $partnership->getId()]);
        }

        /** @var UploadedFile|null $uploadedFile */
        $uploadedFile = $request->files->get('signedContract');
        
        if (!$uploadedFile) {
            $this->logger->error('No file uploaded');
            $this->addFlash('error', 'No file was uploaded or file size exceeds server limit');
            return $this->redirectToRoute('app_organizer_contract_management', ['id' => $partnership->getId()]);
        }

        // Verify file type
        if ($uploadedFile->getMimeType() !== 'application/pdf') {
            $this->addFlash('error', 'Only PDF files are accepted.');
            return $this->redirectToRoute('app_organizer_contract_management', ['id' => $partnership->getId()]);
        }

        try {
            $newFilename = sprintf(
                'contract_%d_signed_%s.%s',
                $partnership->getId(),
                uniqid(),
                $uploadedFile->guessExtension()
            );

            $uploadDir = $this->getParameter('signed_contracts_directory');
            $uploadedFile->move($uploadDir, $newFilename);

            $partnership
                ->setSignedContractFile($newFilename)
                ->setIsSigned(true)
                ->setSignedAt(new \DateTime());

            $entityManager->flush();

            $this->addFlash('success', 'Signed contract uploaded successfully!');
            $this->logger->info('Contract uploaded', [
                'partnership' => $partnership->getId(),
                'file' => $newFilename
            ]);

        } catch (\Exception $e) {
            $this->addFlash('error', 'Upload failed: ' . $e->getMessage());
            $this->logger->error('Upload error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
        }

        return $this->redirectToRoute('app_organizer_contract_management', ['id' => $partnership->getId()]);
    }

    #[Route('/partnership/{id}/verify', name: 'app_organizer_verify_contract', methods: ['GET', 'POST'])]
    public function verifyContract(Request $request, Partnership $partnership): Response
    {
        if ($request->isMethod('POST')) {
            $uploadedFile = $request->files->get('contract');
            
            if (!$uploadedFile) {
                $this->addFlash('error', 'No file was uploaded.');
                return $this->redirectToRoute('app_organizer_verify_contract', ['id' => $partnership->getId()]);
            }

            try {
                $this->logger->info('Starting contract verification process', [
                    'partnershipId' => $partnership->getId(),
                    'filename' => $uploadedFile->getClientOriginalName(),
                    'size' => $uploadedFile->getSize()
                ]);

                $isValid = $this->signatureVerificationService->verifySignature(
                    $uploadedFile->getPathname(),
                    $partnership->getId()
                );
                
                if ($isValid) {
                    $this->addFlash('success', 'Contract verified successfully!');
                    $partnership->setIsSigned(true);
                    $partnership->setSignedAt(new \DateTime());
                    
                    $this->entityManager->persist($partnership);
                    $this->entityManager->flush();
                } else {
                    // Get the original contract size for comparison
                    $originalContractPattern = $this->getParameter('kernel.project_dir') . '/public/uploads/organizer/contracts/contract_' . $partnership->getId() . '_*.pdf';
                    $originalContracts = glob($originalContractPattern);
                    $originalSize = !empty($originalContracts) ? filesize($originalContracts[count($originalContracts) - 1]) : 0;
                    
                    $this->addFlash('error', sprintf(
                        'Contract verification failed. The uploaded file (%d bytes) must be larger than the original contract (%d bytes) to be considered signed.',
                        $uploadedFile->getSize(),
                        $originalSize
                    ));
                }
            } catch (\Exception $e) {
                $this->logger->error('Error during contract verification', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                $this->addFlash('error', 'Error verifying contract: ' . $e->getMessage());
            }
            
            return $this->redirectToRoute('app_organizer_contract_management', ['id' => $partnership->getId()]);
        }

        return $this->render('organizer/verify_contract.html.twig', [
            'partnership' => $partnership,
        ]);
    }
} 