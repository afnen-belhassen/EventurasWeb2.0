<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Categorie; 
use App\Entity\Rating;
use App\Form\EventType;
use App\Entity\Reservation;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use App\Repository\EventRepository;
use App\Repository\ReservationRepository;
final class EventController extends AbstractController
{
    #[Route('/homeOrg', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        // Fetch the last 3 accepted events
        $events = $entityManager->getRepository(Event::class)
            ->findBy(['status' => 'Accepté'], ['creation_date' => 'DESC'], 3);

        return $this->render('service/indexORG.html.twig', [
            'events' => $events,
        ]);
    }
    #[Route('/homePart', name: 'app_home_part')]
    public function indexPart(EntityManagerInterface $entityManager): Response
    {
        // Fetch the last 3 accepted events
        $events = $entityManager->getRepository(Event::class)
            ->findBy(['status' => 'Accepté'], ['creation_date' => 'DESC'], 3);

        return $this->render('service/indexPART.html.twig', [
            'events' => $events,
        ]);
    }
    #[Route('/homeBACK', name: 'app_home_back')]
    public function indexBack(EntityManagerInterface $entityManager,ReservationRepository $reservationRepository): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();
        $reservations = $reservationRepository->findAll();
        return $this->render('back/indexBACK.html.twig', [
            'events' => $events,
            'reservations' => $reservations
        ]);

          
    }
  
    #[Route('/event/new', name: 'app_event_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $event = new Event();

    // Set default values
    $event->setUser_id(2); // Set user_id to 1 by default
    $event->setStatus('En cours de traitement'); // Set status
    $event->setCreation_date(new \DateTime()); // Set creation date to current timestamp

    $form = $this->createForm(EventType::class, $event);

    $form->handleRequest($request);
    if ($form->isSubmitted() && $form->isValid()) {
        // Handle the file upload
        $file = $form->get('image')->getData();
        if ($file) {
            $filename = uniqid() . '.' . $file->guessExtension(); // Generate a unique filename
            $file->move($this->getParameter('images_events_directory'), $filename); // Move the file to the directory

            $event->setImage($filename); // Save the filename in the event entity
        }

        $entityManager->persist($event);
        $entityManager->flush();

        // Redirect to the event details page with the event ID
        return $this->redirectToRoute('app_event_show', ['id' => $event->getId_event()]);
    }

    return $this->render('service/createEvent.html.twig', [
        'form' => $form->createView(),
    ]);
}
#[Route('/event/{id}/edit', name: 'app_event_edit', methods: ['GET', 'POST'])]
public function edit(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $event = $entityManager->getRepository(Event::class)->find($id);
    
    if (!$event) {
        throw $this->createNotFoundException('Event not found');
    }
    
    $form = $this->createForm(EventType::class, $event); // 
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Event updated successfully!');
        return $this->redirectToRoute('app_show_all_events', ['id' => $event->getId()]);
    }
    
    return $this->render('service/editEvent.html.twig', [
        'form' => $form->createView(),
        'event' => $event
    ]);
}

    #[Route('/event/{id}/delete', name: 'app_event_delete', methods: ['POST'])]
    public function delete(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId_event(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_home');
    }
    #[Route('/event/{id}', name: 'app_event_show')]
    public function show(Event $event): Response
    {
    return $this->render('service/showDetails.html.twig', [
        'event' => $event,
    ]);
    }
    #[Route('/events', name: 'app_show_all_events')]
    public function showAll(EntityManagerInterface $entityManager, Request $request): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();
        $highlightId = $request->query->get('highlight');
    
        return $this->render('service/showAll.html.twig', [
            'events' => $events,
            'highlight' => $highlightId
        ]);
    }
    
    #[Route('/eventsBack', name: 'app_show_all_eventsBack')]
    public function showAllBack(EntityManagerInterface $entityManager, Request $request): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('backOFF/displayEveBack.html.twig', [
            'events' => $events,
        ]);
    }
    #[Route('/event/change-status/{id}', name: 'app_change_status', methods: ['POST'])]
    public function changeStatus(int $id, EntityManagerInterface $entityManager): Response
    {
        // Find the event by ID
        $event = $entityManager->getRepository(Event::class)->find($id);

        if (!$event) {
            throw $this->createNotFoundException('Événement non trouvé.');
        }

        // Toggle the status
        if ($event->getStatus() === 'En cours de traitement') {
            $event->setStatus('Accepté');
        } else if ($event->getStatus() === 'Accepté') {
            $event->setStatus('En cours de traitement');
        }

        // Persist the changes
        $entityManager->flush();

        // Redirect or return a response
        return $this->redirectToRoute('app_show_all_eventsBack');
    }
 
        #[Route('/event/get/{id}', name: 'app_event_get')]
        public function getEventData(int $id, EntityManagerInterface $entityManager): JsonResponse
        {
            $event = $entityManager->getRepository(Event::class)->find($id);
    
            if (!$event) {
                return $this->json(['error' => 'Event not found'], 404);
            }
            
            return $this->json([
                'id_event' => $event->getIdEvent(),
                'title' => $event->getTitle(),
                'description' => $event->getDescription(),
                'date_event' => $event->getDateEvent()->format('Y-m-d H:i:s'),
                'dateFinEve' => $event->getDateFinEve()->format('Y-m-d H:i:s'),
                'location' => $event->getLocation(),
                'activities' => $event->getActivities(),
                'prix' => $event->getPrix(),
                'nb_places' => $event->getNbPlaces(),
                'status' => $event->getStatus(),
            ]);
        }
      // In EventController.php
      #[Route('/event/{id}/edit-page', name: 'app_event_edit_page', methods: ['GET', 'POST'])]
      public function editPage(Request $request, Event $event, EntityManagerInterface $entityManager): Response
      {
          $form = $this->createForm(EventType::class, $event); // ⬅️ plus de 'is_edit'
      
          $form->handleRequest($request);
      
          if ($form->isSubmitted()) {
              // Debug before saving
              dump($form->isValid());
              dump($event);
              dump($form->getErrors(true));
              
              if ($form->isValid()) {
                  try {
                      $entityManager->flush();
                      $this->addFlash('success', 'Event updated successfully!');
                      return $this->redirectToRoute('app_show_all_events', [
                          'highlight' => $event->getIdEvent()
                      ]);
                  } catch (\Exception $e) {
                      $this->addFlash('error', 'Error saving: '.$e->getMessage());
                  }
              }
          }
      
          return $this->render('service/editEvent.html.twig', [
              'form' => $form->createView(),
              'event' => $event
          ]);
      }
      #[Route('/search', name: 'app_event_search')]
public function search(Request $request, EventRepository $eventRepository)
{
    $query = $request->query->get('q');
    $events = $eventRepository->findAll(); // Default to all events
    
    if (!empty($query)) {
        $events = $eventRepository->searchByName($query);
    }
    
    return $this->render('service/showAll.html.twig', [ // Reuse the same template
        'events' => $events,
        'search_query' => $query // Pass the query to pre-fill the search input
    ]);
}
#[Route('/event/{id}/editBack', name: 'app_event_edit_back', methods: ['GET', 'POST'])]
public function editBack(Request $request, int $id, EntityManagerInterface $entityManager): Response
{
    $event = $entityManager->getRepository(Event::class)->find($id);
    
    if (!$event) {
        throw $this->createNotFoundException('Event not found');
    }
    
    $form = $this->createForm(EventType::class, $event); // 
    $form->handleRequest($request);
    
    if ($form->isSubmitted() && $form->isValid()) {
        $entityManager->flush();
        $this->addFlash('success', 'Event updated successfully!');
        return $this->redirectToRoute('app_show_all_eventsBack', ['id' => $event->getId()]);
    }
    
    return $this->render('backOFF/editEventBack.html.twig', [
        'form' => $form->createView(),
        'event' => $event
    ]);
}
#[Route('/newBack', name: 'app_eventback')]
public function newBack(Request $request, EntityManagerInterface $entityManager): Response
{
    $event = new Event();
    $form = $this->createForm(EventType::class, $event);
    $event->setUser_id(1);
    $event->setStatus('Accepté');
    $event->setCreation_date(new \DateTime());

 
    $form->handleRequest($request);

    if ($form->isSubmitted() && $form->isValid()) {
        try {
            $file = $form->get('image')->getData();
            if ($file) {
                $filename = uniqid().'.'.$file->guessExtension();
                $file->move($this->getParameter('images_events_directory'), $filename);
                $event->setImage($filename);
            }

            $entityManager->persist($event);
            $entityManager->flush();

            $this->addFlash('success', 'Event created successfully!');
            return $this->redirectToRoute('app_show_all_eventsBack');
        } catch (\Exception $e) {
            $this->addFlash('error', 'Error creating event: '.$e->getMessage());
        }
    }

    return $this->render('backOFF/createEventBack.html.twig', [
        'form' => $form->createView(),
    ]);
}
    #[Route('/events/accepted', name: 'app_accepted_events')]
    public function showAcceptedEvents(EventRepository $eventRepository, Request $request): Response
    {
        $criteria = $request->query->get('sort', 'upcoming');
        $events = $eventRepository->findByCriteria($criteria);
        
        return $this->render('service/displayEventFRONT.html.twig', [
            'events' => $events,
            'criteria' => $criteria
        ]);
    }
    #[Route('/event/{id}/deleteBACK', name: 'app_evedelBACK', methods: ['POST'])]
    public function deleteBACK(Request $request, Event $event, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$event->getId_event(), $request->request->get('_token'))) {
            $entityManager->remove($event);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_show_all_eventsBack');
    }
    // src/Controller/EventController.php

#[Route('/event/{id_event}/rate/{value}', name: 'app_event_rate', methods: ['POST'])]
public function rateEvent(
    Event $event,
    int $value,
    EntityManagerInterface $em,
    Request $request
): Response {
    // For testing, we'll use user_id = 1
    $userId = 2;

    // Check if event is in the past
    if ($event->getDateEvent() > new \DateTime()) {
        return $this->json([
            'success' => false,
            'message' => 'You can only rate past events.'
        ]);
    }

    // Check if user already rated this event
    $existingRating = $em->getRepository(Rating::class)->findOneBy([
        'event' => $event,
        'user_id' => $userId
    ]);

    if ($existingRating) {
        $existingRating->setValue($value);
    } else {
        $rating = new Rating();
        $rating->setEvent($event);
        $rating->setUser_id($userId);
        $rating->setValue(max(1, min(5, $value)));
        $em->persist($rating);
    }

    $em->flush();
    
    return $this->json([
        'success' => true,
        'averageRating' => $event->getAverageRating(),
        'ratingCount' => $event->getRatings()->count()
    ]);
}      

}