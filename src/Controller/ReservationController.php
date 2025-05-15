<?php

namespace App\Controller;
use App\Repository\ReservationRepository;
use App\Entity\Event;
use App\Entity\Reservation;
use App\Entity\Ticket;
use App\Form\ReservationType;
use App\Form\TicketType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Stripe\Stripe;
use Stripe\PaymentIntent;
use App\Service\TwilioService;
use Symfony\Bundle\SecurityBundle\Security;
use App\Entity\Users;
class ReservationController extends AbstractController
{
    #[Route('/event/{id}/reserve', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $id, EntityManagerInterface $entityManager,Security $security): Response
    {
        $reservation = new Reservation();
        $event = $entityManager->getRepository(Event::class)->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }
        $user = $security->getUser();
        $userId = $user->getUserId();
        $reservation->setEvent($event);
        $reservation->setUser_id($userId);
        $reservation->setStatus('pending');

        // Get available seats
        $reservedSeats = $entityManager->getRepository(Reservation::class)
            ->findReservedSeatsForEvent($id);
        $allSeats = range(1, $event->getNbPlaces());
        $availableSeats = array_diff($allSeats, $reservedSeats);

        $form = $this->createForm(ReservationType::class, $reservation, [
            'available_seats' => $availableSeats
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $seatNumber = $form->get('seat_number')->getData();
            
            if (in_array($seatNumber, $reservedSeats)) {
                $this->addFlash('error', 'This seat has been taken. Please choose another.');
                return $this->redirectToRoute('app_reservation_new', ['id' => $id]);
            }

            $ticket = new Ticket();
            $ticketCode = $this->generateTicketCode($seatNumber, $event->getTitle());
            $ticket->setTicketCode($ticketCode);
            $ticket->setSeat_number((string)$seatNumber);
            
            $entityManager->persist($ticket);
            $entityManager->flush();

            $reservation->setTicket($ticket);
            $reservation->setStatus('reserved');

            $entityManager->persist($reservation);
            $entityManager->flush();

            $this->addFlash('success', 'Reservation created successfully!');
            return $this->redirectToRoute('app_ticket_show', ['id' => $ticket->getTicketId()]);
        }

        return $this->render('reservation/new.html.twig', [
            'form' => $form->createView(),
            'event' => $event,
        ]);
    }

    #[Route('/reservation/{id}', name: 'app_reservation_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation not found');
        }

        return $this->render('reservation/show.html.twig', [
            'reservation' => $reservation,
        ]);
    }

    #[Route('/reservation/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function cancel(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = $entityManager->getRepository(Reservation::class)->find($id);
        
        if (!$reservation) {
            throw $this->createNotFoundException('Reservation not found');
        }

        if ($this->isCsrfTokenValid('cancel'.$reservation->getId(), $request->request->get('_token'))) {
            $reservation->setStatus('cancelled');
            $entityManager->flush();
            $this->addFlash('success', 'Reservation cancelled successfully.');
        }

        return $this->redirectToRoute('app_user_reservations');
    }

    #[Route('/my-reservations', name: 'app_user_reservations', methods: ['GET'])]
    public function userReservations(EntityManagerInterface $entityManager): Response
    {
        $reservations = $entityManager->getRepository(Reservation::class)
            ->findBy(['user' => $this->getUser()], ['id' => 'DESC']);

        return $this->render('reservation/user_list.html.twig', [
            'reservations' => $reservations,
        ]);
    }

    private function generateTicketCode(int $seatNumber, string $eventName): string
    {
        $prefix = str_pad($seatNumber, 2, '0', STR_PAD_LEFT);
        $initials = '';
        
        $words = explode(' ', $eventName);
        foreach ($words as $word) {
            if (!empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }
        
        if (strlen($initials) > 2) {
            $initials = substr($initials, 0, 2);
        }
        
        return $prefix . 'TCT' . $initials;
    }

    #[Route('/ticket/{id}/view', name: 'app_ticket_view', methods: ['GET'])]
    public function viewTicket(int $id, EntityManagerInterface $entityManager): Response
    {
        $ticket = $entityManager->getRepository(Ticket::class)->find($id);
        
        if (!$ticket) {
            throw $this->createNotFoundException('Ticket not found');
        }

        $form = $this->createForm(TicketType::class, $ticket, [
            'action' => $this->generateUrl('app_ticket_view', ['id' => $id]),
            'method' => 'GET'
        ]);

        return $this->render('ticket/view.html.twig', [
            'ticket' => $ticket,
            'form' => $form->createView()
        ]);
    }

    #[Route('/event/{id_event}/seats', name: 'app_seat_selection')]
    public function seatSelection(int $id_event, EntityManagerInterface $em, ParameterBagInterface $params): Response
    {
        $event = $em->getRepository(Event::class)->find($id_event);
        $reservedSeats = $em->getRepository(Reservation::class)
            ->findReservedSeatsForEvent($id_event);

        return $this->render('reservation/chooseSeat.html.twig', [
            'event' => $event,
            'reservedSeats' => $reservedSeats,
            'stripe_public_key' => $params->get('stripe_public_key')
        ]);
    }

    #[Route('/create-payment-intent', name: 'app_create_payment_intent', methods: ['POST'])]
    public function createPaymentIntent(Request $request, EntityManagerInterface $em): JsonResponse
    {
        try {
            $data = json_decode($request->getContent(), true);
            
            if (!isset($data['event_id']) || empty($data['event_id'])) {
                throw new \Exception('Event ID is required');
            }
            
            if (!isset($data['seat_number']) || empty($data['seat_number'])) {
                throw new \Exception('Seat number is required');
            }

            $event = $em->getRepository(Event::class)->find($data['event_id']);
            if (!$event) {
                throw new \Exception('Event not found');
            }

            Stripe::setApiKey($this->getParameter('stripe_secret_key'));

            $paymentIntent = PaymentIntent::create([
                'amount' => $event->getPrix() * 100,
                'currency' => 'usd',
                'metadata' => [
                    'event_id' => $event->getId_event(),
                    'seat_number' => $data['seat_number'],
                    'original_currency' => 'TND',
                    'original_amount' => $event->getPrix()
                ],
                'payment_method_types' => ['card'],
            ]);

            return $this->json([
                'success' => true,
                'clientSecret' => $paymentIntent->client_secret
            ]);

        } catch (\Exception $e) {
            return $this->json([
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/payment/success', name: 'app_payment_success')]
    public function paymentSuccess(Request $request, EntityManagerInterface $em): Response
    {
        $paymentIntentId = $request->query->get('payment_intent');
        
        if (!$paymentIntentId) {
            throw $this->createNotFoundException('Payment ID missing');
        }

        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        $reservation = $em->getRepository(Reservation::class)
            ->findOneBy(['stripePaymentId' => $paymentIntentId]);

        if (!$reservation) {
            $eventId = $paymentIntent->metadata->event_id ?? null;
            $seatNumber = $paymentIntent->metadata->seat_number ?? null;

            if ($eventId && $seatNumber) {
                return $this->redirectToRoute('app_confirm_reservation', [
                    'payment_intent_id' => $paymentIntentId,
                    'event_id' => $eventId,
                    'seat_number' => $seatNumber
                ]);
            }
            
            throw $this->createNotFoundException('Reservation not found');
        }
        $event = $em->getRepository(Event::class)->find($reservation->getEvent());
        return $this->render('reservation/reservation_success.html.twig', [
            'reservation' => $reservation,
            'event' => $event,
            'paymentIntent' => $paymentIntent
        ]);
    }

    #[Route('/confirm-reservation', name: 'app_confirm_reservation', methods: ['POST'])]
    public function confirmReservation(Request $request, EntityManagerInterface $em, TwilioService $twilioService = null,Security $security): JsonResponse
    {
        try {
            // Log the incoming request for debugging
            error_log('Confirm request content: ' . $request->getContent());
            error_log('Content type: ' . $request->headers->get('Content-Type'));
            
            // Parse JSON data
            $data = json_decode($request->getContent(), true);
            if (json_last_error() !== JSON_ERROR_NONE) {
                throw new \Exception('Invalid JSON: ' . json_last_error_msg());
            }
            
            // Validate input
            if (!isset($data['payment_intent_id']) || !isset($data['event_id']) || !isset($data['seat_number'])) {
                throw new \Exception('Missing required parameters');
            }
            
            error_log('Data received: ' . print_r($data, true));

            $eventId = $data['event_id'];
            $seatNumber = $data['seat_number'];
            $paymentIntentId = $data['payment_intent_id'];

            // Fetch the event
            $event = $em->getRepository(Event::class)->find($eventId);
            if (!$event) {
                throw new \Exception("Event not found with ID: $eventId");
            }
            
            // Skip Stripe verification for troubleshooting
            // In production, uncomment this
            /*
            Stripe::setApiKey($this->getParameter('stripe_secret_key'));
            $paymentIntent = PaymentIntent::retrieve($paymentIntentId);
            if ($paymentIntent->status !== 'succeeded') {
                throw new \Exception('Payment failed. Please try again.');
            }
            */

            // Check for existing reservation
            $existingReservation = $em->getRepository(Reservation::class)
                ->findOneBy(['stripePaymentId' => $paymentIntentId]);

            if ($existingReservation) {
                return $this->json([
                    'success' => true,
                    'message' => 'Reservation already exists',
                    'redirect' => $this->generateUrl('app_payment_success', [
                        'payment_intent' => $paymentIntentId
                    ])
                ]);
            }

            // Create new reservation
            $currentDate = new \DateTime();

            if ($event->getDateEvent() < $currentDate) {
                throw new \Exception('This event has already taken place.');
            }

            // Create ticket and reservation
            $ticket = new Ticket();
            $ticket->setSeat_number($seatNumber);
            $ticket->setTicketCode($this->generateTicketCode(intval($seatNumber), $event->getTitle()));
            $em->persist($ticket);
            
            // Update available seats count
            $event->setNbPlaces($event->getNbPlaces() - 1);
            $em->persist($event);
            
            $user = $security->getUser();
            $userId = $user->getUserId();

            $reservation = new Reservation();
            $reservation->setEvent($event);
            $reservation->setUser_id($userId);
            $reservation->setStatus('confirmed');
            $reservation->setStripePaymentId($paymentIntentId);
            $reservation->setTicket($ticket);
            $em->persist($reservation);
            
            $em->flush();
            
            error_log("Reservation created successfully with ID: " . $reservation->getId());

            // Send SMS notification if Twilio service is available
            if ($twilioService !== null) {
                try {
                    $userPhoneNumber = '+21622423094'; // In a real app, get from user profile
                    $twilioService->sendSms($userPhoneNumber, 'Paiement effectué avec succès');
                } catch (\Exception $smsError) {
                    // Log SMS error but don't fail the reservation
                    error_log('SMS notification error: ' . $smsError->getMessage());
                }
            }

            return $this->json([
                'success' => true,
                'message' => 'Reservation confirmed successfully',
                'redirect' => $this->generateUrl('app_payment_success', [
                    'payment_intent' => $paymentIntentId
                ])
            ]);

        } catch (\Exception $e) {
            error_log('Error in confirmReservation: ' . $e->getMessage() . "\n" . $e->getTraceAsString());
            
            return $this->json([
                'success' => false,
                'error' => $e->getMessage()
            ], 400);
        }
    }

    #[Route('/reservations', name: 'app_reservations')]
public function user1Reservations(ReservationRepository $reservationRepo, Security $security): Response
{
    $user = $security->getUser();
    $userId = $user->getUserId();
    $userFirstName = $user->getUserFirstname();

    return $this->render('reservation/displayReservation.html.twig', [
        'reservations' => $reservationRepo->findBy(['user_id' => $userId]),
        'username' => $userFirstName,
    ]);
}


    #[Route('/reservation/{id}/cancel', name: 'app_reservation_cancel', methods: ['POST'])]
    public function annuler(int $id, ReservationRepository $reservationRepository, EntityManagerInterface $entityManager): Response
    {
        $reservation = $reservationRepository->find($id);
        if (!$reservation) {
            return $this->json(['success' => false, 'message' => 'Réservation non trouvée'], 404);
        }
        if ($reservation->getStatus() === 'annulee') {
            return $this->json(['success' => false, 'message' => 'Réservation déjà annulée'], 400);
        }
        $reservation->setStatus('annulee');
        $entityManager->flush();
        return $this->json(['success' => true, 'message' => 'Réservation annulée avec succès.']);
    }
}