<?php

namespace App\Controller;
use App\Entity\Reservation;
use App\Entity\Ticket;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\SecurityBundle\Security;

class TicketController extends AbstractController
{
    #[Route('/ticket/{id}', name: 'app_ticket_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);
        
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        return $this->render('ticket/show.html.twig', [
            'ticket' => $ticket,
        ]);
    }

    #[Route('/ticket/{id}/download', name: 'app_ticket_download', methods: ['GET'])]
    public function download(int $id, EntityManagerInterface $entityManager): Response
    {
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);
        
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        // Generate PDF or other downloadable format
        $html = $this->renderView('ticket/pdf.html.twig', [
            'ticket' => $ticket
        ]);

        return new Response($html, 200, [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'attachment; filename="ticket_'.$ticket->getTicketCode().'.pdf"'
        ]);
    }

    #[Route('/my-tickets', name: 'app_user_tickets', methods: ['GET'])]
    public function userTickets(EntityManagerInterface $entityManager,Security $security): Response
    {
        $user = $security->getUser();
        $userId = $user->getUserId();
        // Get reservations for user 1
        $reservations = $entityManager->getRepository(Reservation::class)
            ->findBy(['user_id' => $userId]); // Hardcoded user ID 1

        // Get ticket IDs from reservations
        $ticketIds = array_map(function($reservation) {
            return $reservation->getTicketId();
        }, $reservations);

        // Get tickets
        $tickets = $entityManager->getRepository(Ticket::class)
            ->findBy(['ticket_id' => $ticketIds]);

        return $this->render('ticket/user_list.html.twig', [
            'tickets' => $tickets,
        ]);
    }
}