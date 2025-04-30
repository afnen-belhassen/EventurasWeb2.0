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

class ReservationController extends AbstractController
{
    #[Route('/event/{id}/reserve', name: 'app_reservation_new', methods: ['GET', 'POST'])]
    public function new(Request $request, int $id, EntityManagerInterface $entityManager): Response
    {
        $reservation = new Reservation();
        $event = $entityManager->getRepository(Event::class)->find($id);
        
        if (!$event) {
            throw $this->createNotFoundException('Event not found');
        }

        $reservation->setEvent($event);
        $reservation->setUser_id(1);
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
    public function confirmReservation(Request $request, EntityManagerInterface $em, TwilioService $twilioService): Response
    {
        // Retrieve data from the request
        $paymentIntentId = $request->request->get('payment_intent_id');
        $eventId = $request->request->get('event_id');
        $seatNumber = $request->request->get('seat_number');

        // Process the payment
        Stripe::setApiKey($this->getParameter('stripe_secret_key'));
        $paymentIntent = PaymentIntent::retrieve($paymentIntentId);

        if ($paymentIntent->status !== 'succeeded') {
            $this->addFlash('error', 'Payment failed. Please try again.');
            return $this->redirectToRoute('app_seat_selection', ['id_event' => $eventId]);
        }

        $existingReservation = $em->getRepository(Reservation::class)
            ->findOneBy(['stripePaymentId' => $paymentIntentId]);

        if ($existingReservation) {
            return $this->redirectToRoute('app_payment_success', [
                'payment_intent' => $paymentIntentId
            ]);
        }

        // Create a new reservation and ticket
        $event = $em->getRepository(Event::class)->find($eventId);
        $ticket = new Ticket();
        $ticket->setSeatNumber($seatNumber);
        $ticket->setTicketCode($this->generateTicketCode($seatNumber, $event->getTitle()));
        $event = $em->getRepository(Event::class)->find($eventId);
        $em->persist($ticket);
        $em->flush();
        $currentDate = new \DateTime();
        if ($event->getDateEvent() < $currentDate) {
            $this->addFlash('error', 'This event has already taken place.');
            return $this->redirectToRoute('app_seat_selection', ['id_event' => $eventId]);
        }
        if ($event->getNbPlaces() <= 0) {
            $this->addFlash('error', 'No available places left for this event.');
            return $this->redirectToRoute('app_seat_selection', ['id_event' => $eventId]);
        }
        
        // Decrease the number of available places
        $event->setNbPlaces($event->getNbPlaces() - 1);
        $em->persist($event);
        $reservation = new Reservation();
        $reservation->setEvent($event);
        $reservation->setUser_id(1); // Example user ID, replace as needed
        $reservation->setStatus('confirmed');
        $reservation->setStripePaymentId($paymentIntentId);
        $reservation->setPaymentStatus('paid');
        $reservation->setTicket($ticket);
        
        $em->persist($reservation);
        $em->flush();

        // Send SMS to the user (you can dynamically get the phone number or use a static one)
        $userPhoneNumber = '+21622423094'; // Replace with the actual user's phone number
        $twilioService->sendSms($userPhoneNumber, 'Paiement effectué avec succès');

        return $this->redirectToRoute('app_payment_success', [
            'payment_intent' => $paymentIntentId
        ]);
    }

}