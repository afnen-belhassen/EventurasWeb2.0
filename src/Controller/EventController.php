<?php
namespace App\Controller;

use App\Entity\Event;
use App\Entity\Categorie; // Import the Categorie entity
use App\Form\EventType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

final class EventController extends AbstractController
{
    #[Route('/home', name: 'app_home')]
    public function index(EntityManagerInterface $entityManager): Response
    {
        $events = $entityManager->getRepository(Event::class)->findAll();

        return $this->render('back/indexBACK.html.twig', [
            'events' => $events,
        ]);
    }

    #[Route('/event/new', name: 'app_event_new')]
public function new(Request $request, EntityManagerInterface $entityManager): Response
{
    $event = new Event();

    // Set default values
    $event->setUser_id(1); // Set user_id to 1 by default
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
#[Route('/event/{id}/edit', name: 'app_event_edit')]
public function edit(Request $request, Event $event, EntityManagerInterface $entityManager): Response
{
    $form = $this->createForm(EventType::class, $event); // Pass the existing event
    $form->handleRequest($request);

    if ($form->isSubmitted()) {
        dump($form->getData()); // Check the submitted data

        if (!$form->isValid()) {
            dump($form->getErrors(true, false)); // Check for validation errors
        }

        if ($form->isValid()) {
            // Update only the fields you want to change
            $event->setTitle($form->get('title')->getData() ?? $event->getTitle());
            $event->setDate_event($form->get('date')->getData() ?? $event->getDate_event());
            $event->setPrix($form->get('prix')->getData() ?? $event->getPrix());
            $event->setLocation($form->get('localisation')->getData() ?? $event->getLocation());

          try {
        $entityManager->flush(); // Save changes
        } catch (\Exception $e) {
        dump($e->getMessage()); // Log any exceptions
        }

            $this->addFlash('success', 'Event updated successfully!'); // Set success message

            // Redirect to the showAll template after editing
            return $this->redirectToRoute('app_show_all_events');
        }
    }

    // Render the form if not submitted or if there are validation errors
    return $this->render('service/indexORG.html.twig', [
        'form' => $form->createView(),
        'event' => $event, // Pass the event for editing
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
    public function showAll(EntityManagerInterface $entityManager): Response
    {
    $events = $entityManager->getRepository(Event::class)->findAll();

    return $this->render('service/showAll.html.twig', [
        'events' => $events,
    ]);
    }
}