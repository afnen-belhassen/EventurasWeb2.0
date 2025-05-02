<?php

namespace App\Controller;

use App\Entity\ConversationMessage;
use App\Entity\MessageAttachment;
use App\Entity\Reclamation;
use App\Entity\ReclamationAttachment;
use App\Entity\ReclamationConversation;
use App\Form\ConversationMessageType;
use App\Form\ReclamationType;
use App\Repository\ReclamationConversationRepository;
use App\Service\EmailJSService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\RateLimiter\RateLimiterFactory;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\UX\Chartjs\Model\Chart;
use Symfony\UX\Chartjs\Builder\ChartBuilderInterface;
use App\Repository\ReclamationRepository;



final class ReclamationController extends AbstractController
{


    #[Route('/reclamsBack', name: 'app_show_all_reclamsBack')]
    public function showReclamsBack(
        EntityManagerInterface $entityManager,
        Request $request,
        PaginatorInterface $paginator
    ): Response {
        $status = $request->query->get('status');
    
        $qb = $entityManager->getRepository(Reclamation::class)->createQueryBuilder('r');
    
        if ($status && $status !== 'all') {
            $qb->where('r.status = :status')
               ->setParameter('status', $status);
        }
    
        $qb->orderBy('r.created_at', 'ASC');
    
        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            10
        );
    
        return $this->render('backOFF/reclamsBack.html.twig', [
            'reclamations'   => $pagination,
            'selectedStatus' => $status,
        ]);
    }
    




    #[Route('/reclamations', name: 'app_show_all_reclamations', methods: ['GET'])]
    public function showAll(
        Request $request,
        EntityManagerInterface $em,
        PaginatorInterface $paginator
    ): Response {
        $statusFilter = $request->query->get('status');
    
        $qb = $em->getRepository(Reclamation::class)->createQueryBuilder('r');
    
        if ($statusFilter && $statusFilter !== 'all') {
            $qb->where('r.status = :status')
               ->setParameter('status', $statusFilter);
        }
    
        $qb->orderBy('r.created_at', 'ASC');
    
        $pagination = $paginator->paginate(
            $qb,
            $request->query->getInt('page', 1),
            6
        );
    
        $newRecl = new Reclamation();
        $newRecl->setStatus('En attente');
        $form = $this->createForm(ReclamationType::class, $newRecl);
    
        return $this->render('reclamation/reclam.html.twig', [
            'reclamations'    => $pagination,
            'form'            => $form->createView(),
            'selectedStatus'  => $statusFilter, // Pass the current filter to the template
        ]);
    }

    #[Route('/reclamation/{id}/delete', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete' . $reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_show_all_reclamations');
    }


    #[Route('/reclamation/new', name: 'app_reclamation_new', methods: ['POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $em,
        #[Autowire(service: 'limiter.reclamation_submitter')]
        RateLimiterFactory $reclamationSubmitter
    ): Response {
        // Identify the user (for now, use IP)
        $limiter = $reclamationSubmitter->create($request->getClientIp());

        // Consume 1 token
        $limit = $limiter->consume(1);
        if (!$limit->isAccepted()) {
            $retryAfter = $limit->getRetryAfter()?->diff(new \DateTimeImmutable())->format('%h heures %i minutes');
            $this->addFlash('rate_limit', 'Vous avez déjà soumis une réclamation. Veuillez réessayer dans ' . $retryAfter . '.');
            return $this->redirectToRoute('app_show_all_reclamations');
        }
        $reclamation = new Reclamation();
        $reclamation->setStatus('En attente');
        $reclamation->setIdUser(2); // hardcoded for now


        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($reclamation);

            /** @var UploadedFile[] $files */
            $files = $form->get('attachments')->getData();
            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }

                $origName    = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName    = preg_replace('/[^a-z0-9_]+/i', '_', $origName);
                $newFilename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('attachments_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // log or handle exception as needed
                }

                $attach = new ReclamationAttachment();
                $attach->setFilePath($newFilename);
                $attach->setReclamation($reclamation);
                $em->persist($attach);
            }

            $em->flush();
            $this->addFlash('success', 'Réclamation créée avec succès');
        } else {
            $this->addFlash('error', 'Erreur lors de la création de la réclamation');
        }

        return $this->redirectToRoute('app_show_all_reclamations');
    }




    #[Route('/reclamation/{id}/edit', name: 'app_reclamation_edit', methods: ['POST'])]
    public function edit(
        Reclamation $reclamation,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 1) grab the entire POST payload
        $post = $request->request->all();

        // 2) extract your “reclamation” sub‑array (or fallback to empty array)
        $data = [];
        if (isset($post['reclamation']) && is_array($post['reclamation'])) {
            $data = $post['reclamation'];
        }

        // 3) apply updates, falling back to current values
        $reclamation
            ->setIdUser((int)($data['id_user']   ?? $reclamation->getIdUser()))
            ->setIdEvent(isset($data['id_event']) ? (int)$data['id_event'] : null)
            ->setDescription($data['description'] ?? $reclamation->getDescription())
            ->setSubject($data['subject']       ?? $reclamation->getSubject())
        ;

        $em->flush();
        $this->addFlash('success', 'Réclamation mise à jour !');

        return $this->redirectToRoute('app_show_all_reclamations');
    }





    #[Route('/reclamation/{id}/accept', name: 'reclamation_accept', methods: ['POST'])]
    public function accept(
        Reclamation $reclamation,
        EntityManagerInterface $em,
        EmailJSService $emailJSService
    ): JsonResponse {
        $reclamation->setStatus('En cours');

        $conversation = new ReclamationConversation();
        $conversation->setReclamation($reclamation);
        $conversation->setCreatedAt(new \DateTimeImmutable());
        $conversation->setStatus('active');

        $em->persist($conversation);
        $em->flush();

        // Hardcoded user (for now)
        $email = 'bear.crush69@gmail.com';
        $name = 'Test User';

        $emailSent = $emailJSService->sendAcceptanceEmail($name, $email);

        return new JsonResponse(['success' => true, 'email_sent' => $emailSent]);
    }


    #[Route('/reclamation/{id}/refuse', name: 'reclamation_refuse', methods: ['POST'])]
    public function refuse(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $em,
        EmailJSService $emailJSService
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);
        $reason = $data['reason'] ?? null;

        if (!$reason) {
            return new JsonResponse(['error' => 'Raison manquante.'], 400);
        }

        $reclamation->setStatus('Rejeté');
        $reclamation->setRefuseReason($reason);
        $em->flush();

        // Temporary hardcoded user data
        $email = 'bear.crush69@gmail.com';
        $name = 'Test User';

        $emailSent = $emailJSService->sendRefusalEmail($name, $email, $reason);

        return new JsonResponse(['success' => true, 'email_sent' => $emailSent]);
    }



    #[Route('/reclamation/{id}/conversation', name: 'reclamation_conversation', methods: ['GET'])]
    public function showConversation(
        Reclamation $reclamation,
        ReclamationConversationRepository $conversationRepo
    ): Response {
        $conversation = $conversationRepo->findOneBy(['reclamation' => $reclamation]);

        if (!$conversation) {
            throw $this->createNotFoundException('No conversation found.');
        }

        $form = $this->createForm(ConversationMessageType::class);

        return $this->render('reclamation/reclamConvo.html.twig', [
            'reclamation' => $reclamation,
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form->createView(),
            'showRatingPopup' => !$reclamation->isRated(), // 🔥 This flag controls the popup
        ]);
    }


    #[Route('/reclamationn/{id}/conversation', name: 'reclamation_conversation_user', methods: ['GET'])]
    public function showConversationUser(
        Reclamation $reclamation,
        ReclamationConversationRepository $conversationRepo
    ): Response {
        $conversation = $conversationRepo->findOneBy(['reclamation' => $reclamation]);

        if (!$conversation) {
            throw $this->createNotFoundException('No conversation found.');
        }

        $form = $this->createForm(ConversationMessageType::class);

        return $this->render('reclamation/reclamConvoUser.html.twig', [
            'reclamation' => $reclamation,
            'conversation' => $conversation,
            'messages' => $conversation->getMessages(),
            'form' => $form->createView(),
            'showRatingPopup' => !$reclamation->isRated(), // 🔥 This flag controls the popup
        ]);
    }










    #[Route('/reclamation/{id}/conversation/send', name: 'reclamation_conversation_send', methods: ['POST'])]
    public function createMessage(
        Reclamation $reclamation,
        Request $request,
        ReclamationConversationRepository $conversationRepo,
        EntityManagerInterface $em
    ): Response {
        $conversation = $conversationRepo->findOneBy(['reclamation' => $reclamation]);

        if (!$conversation) {
            throw $this->createNotFoundException('No conversation found.');
        }

        $message = new ConversationMessage();
        $message->setConversation($conversation);
        $message->setCreatedAt(new \DateTime());
        $message->setSenderId(1); // Replace later with $this->getUser()->getId()

        $form = $this->createForm(ConversationMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
            if (!$form->isValid()) {
                dump($form->getErrors(true, false));
                die('Form is invalid');
            } {
            $em->persist($message);

            $files = $form->get('attachments')->getData();

            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }

                $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-z0-9_]+/i', '_', $origName);
                $newFilename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('conversations_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    continue; // or log the error
                }

                $attachment = new MessageAttachment();
                $attachment->setMessage($message);
                $attachment->setFilePath($newFilename);
                $attachment->setUploadedAt(new \DateTime());

                $em->persist($attachment);
            }

            $em->flush();
        }

        return $this->redirectToRoute('reclamation_conversation', ['id' => $reclamation->getId()]);
    }


    #[Route('/reclamationn/{id}/conversation/send', name: 'reclamation_conversation_send_user', methods: ['POST'])]
    public function createMessageUser(
        Reclamation $reclamation,
        Request $request,
        ReclamationConversationRepository $conversationRepo,
        EntityManagerInterface $em
    ): Response {
        $conversation = $conversationRepo->findOneBy(['reclamation' => $reclamation]);

        if (!$conversation) {
            throw $this->createNotFoundException('No conversation found.');
        }

        $message = new ConversationMessage();
        $message->setConversation($conversation);
        $message->setCreatedAt(new \DateTime());
        $message->setSenderId(2); // Replace later with $this->getUser()->getId()

        $form = $this->createForm(ConversationMessageType::class, $message);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid())
            if (!$form->isValid()) {
                dump($form->getErrors(true, false));
                die('Form is invalid');
            } {
            $em->persist($message);

            $files = $form->get('attachments')->getData();

            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }

                $origName = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName = preg_replace('/[^a-z0-9_]+/i', '_', $origName);
                $newFilename = $safeName . '-' . uniqid() . '.' . $file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('conversations_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    continue; // or log the error
                }

                $attachment = new MessageAttachment();
                $attachment->setMessage($message);
                $attachment->setFilePath($newFilename);
                $attachment->setUploadedAt(new \DateTime());

                $em->persist($attachment);
            }

            $em->flush();
        }

        return $this->redirectToRoute('reclamation_conversation_user', ['id' => $reclamation->getId()]);
    }



    #[Route('/conversation/attachment/{id}/delete', name: 'conversation_attachment_delete', methods: ['DELETE'])]
    public function deleteAttachment(MessageAttachment $attachment, EntityManagerInterface $em): Response
    {
        $em->remove($attachment);
        $em->flush();
        return new Response(null, 204);
    }

    #[Route('/conversation/message/{id}/update', name: 'conversation_message_update', methods: ['POST'])]
    public function updateMessage(ConversationMessage $message, Request $request, EntityManagerInterface $em): Response
    {
        $newContent = $request->request->get('message');
        $message->setMessage($newContent);
        $em->flush();
        return new Response(null, 200);
    }

    #[Route('/conversation/message/{id}/delete', name: 'conversation_message_delete', methods: ['DELETE'])]
    public function deleteMessage(ConversationMessage $message, EntityManagerInterface $em): Response
    {
        $em->remove($message);
        $em->flush();
        return new Response(null, 204);
    }


    #[Route('/conversation/{id}/satisfait', name: 'conversation_mark_resolved', methods: ['POST'])]
    public function markConversationResolved(
        ReclamationConversation $conversation,
        EntityManagerInterface $em
    ): Response {
        $reclamation = $conversation->getReclamation();

        if ($reclamation->getStatus() === 'Résolu') {
            $this->addFlash('info', 'Cette conversation est déjà résolue.');
        } else {
            $reclamation->setStatus('Résolu');
            $reclamation->setClosedAt(new \DateTimeImmutable());
            $em->flush();

            $this->addFlash('success', 'Réclamation marquée comme résolue.');
        }

        return $this->redirectToRoute('reclamation_conversation', [
            'id' => $reclamation->getId()
        ]);
    }


    #[Route('/reclamation/{id}/rate', name: 'rate_reclamation', methods: ['POST'])]
    public function rate(
        Request $request,
        Reclamation $reclamation,
        EntityManagerInterface $em
    ): JsonResponse {
        if ($reclamation->isRated()) {
            return new JsonResponse(['success' => false, 'message' => 'Réclamation déjà notée.']);
        }

        $data = json_decode($request->getContent(), true);
        $rating = $data['rating'] ?? null;

        if (!in_array((int)$rating, [1, 2, 3, 4, 5])) {
            return new JsonResponse(['success' => false, 'message' => 'Note invalide.']);
        }

        $reclamation->setRating((int) $rating);
        $reclamation->setIsRated(true);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }


    private $reclamationRepository;

    public function __construct(ReclamationRepository $reclamationRepository)
    {
        $this->reclamationRepository = $reclamationRepository;
    }

    #[Route('/reclams/stats', name: 'reclam_stats')]
    public function showStats(ChartBuilderInterface $chartBuilder, EntityManagerInterface $em): Response
    {
        $repo = $em->getRepository(Reclamation::class);

        // 1️⃣ Satisfaction by rating
        $satisfactionData = $repo->createQueryBuilder('r')
            ->select('r.rating as rating, COUNT(r.id) as count')
            ->where('r.rating IS NOT NULL')
            ->groupBy('r.rating')
            ->getQuery()
            ->getResult();

        $ratings = array_column($satisfactionData, 'rating');
        $counts  = array_column($satisfactionData, 'count');

        $satisfactionChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $satisfactionChart->setData([
            'labels' => $ratings,
            'datasets' => [[
                'label' => 'Satisfaction (par note)',
                'backgroundColor' => '#4caf50',
                'data' => $counts,
            ]]
        ]);

        // 2️⃣ Reclamations per day (last 15 days)
        $volumeData = $repo->createQueryBuilder('r')
            ->select('r.created_at as day, COUNT(r.id) as count')
            ->groupBy('r.created_at')
            ->orderBy('r.created_at', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult();

        $days = array_map(
            fn($row) => $row['day']->format('Y-m-d'),
            $volumeData
        );

        $perDay = array_column($volumeData, 'count');

        $volumeChart = $chartBuilder->createChart(Chart::TYPE_LINE);
        $volumeChart->setData([
            'labels' => $days,
            'datasets' => [[
                'label' => 'Réclamations / jour',
                'borderColor' => '#2196f3',
                'data' => $perDay,
            ]]
        ]);

        // 3️⃣ Average open-to-close time
        $durations = $repo->createQueryBuilder('r')
            ->select('r.created_at, r.closed_at')
            ->where('r.closed_at IS NOT NULL')
            ->getQuery()
            ->getResult();

        $testChart = $chartBuilder->createChart(Chart::TYPE_BAR);
        $testChart->setData([
            'labels' => ['A', 'B', 'C'],
            'datasets' => [[
                'label' => 'Test Chart',
                'data' => [1, 2, 3],
                'backgroundColor' => ['#f00', '#0f0', '#00f'],
            ]]
        ]);



        $totalSeconds = 0;
        $count = 0;

        foreach ($durations as $row) {
            $createdAt = $row['created_at'];
            $closed_at  = $row['closed_at'];
            if ($createdAt && $closed_at) {
                $totalSeconds += $closed_at->getTimestamp() - $createdAt->getTimestamp();
                $count++;
            }
        }

        $avgHours = $count > 0 ? round($totalSeconds / $count / 3600, 2) : 0;

        $durationChart = $chartBuilder->createChart(Chart::TYPE_DOUGHNUT);
        $durationChart->setData([
            'labels' => ['Temps moyen (h)', 'Restant'],
            'datasets' => [[
                'data' => [$avgHours, 24 - $avgHours],
                'backgroundColor' => ['#ff9800', '#e0e0e0']
            ]]
        ]);

        // Test data for demonstration
        $totalReclamations = 150;
        $pendingReclamations = 45;
        $resolvedReclamations = 85;
        $rejectedReclamations = 20;
        
        $ratingStats = $this->reclamationRepository->getRatingStats();
        $monthlyStats = $this->reclamationRepository->getMonthlyReclamations();
        $avgResolutionTime = $this->reclamationRepository->getAverageResolutionTime();



        
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

        return $this->render('backOFF/reclamStats.html.twig', [
            'satisfactionChart' => $satisfactionChart,
            'volumeChart'       => $volumeChart,
            'durationChart'     => $durationChart,
            'avgHours'          => $avgHours,
            'testChart'         => $testChart, // ✅ Add this line
            'totalReclamations' => $totalReclamations,
            'pendingReclamations' => $pendingReclamations,
            'resolvedReclamations' => $resolvedReclamations,
            'avgResponseTime' => 2.5,
            'monthlyStats' => $monthlyStats,
            'typeDistribution' => $typeDistribution,
            'statusDistribution' => $statusDistribution,
            'avgRating' => $ratingStats['average'],
            'ratingDistribution' => $ratingStats['distribution'],
            'monthlyStats' => $monthlyStats,
            'avgResolutionTime' => $avgResolutionTime,

        ]);
    }
}
