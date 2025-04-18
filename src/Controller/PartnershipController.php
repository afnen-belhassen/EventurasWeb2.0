<?php

namespace App\Controller;

use App\Entity\Partnership;
use App\Form\PartnershipType;
use App\Repository\PartnershipRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/partnership')]
final class PartnershipController extends AbstractController
{
    #[Route(name: 'app_partnership_index', methods: ['GET'])]
    public function index(PartnershipRepository $partnershipRepository): Response
    {
        return $this->render('service/partnership/index.html.twig', [
            'partnerships' => $partnershipRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partnership_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $partnership = new Partnership();
        $form = $this->createForm(PartnershipType::class, $partnership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partnership);
            $entityManager->flush();

            return $this->redirectToRoute('app_partnership_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/partnership/new.html.twig', [
            'partnership' => $partnership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partnership_show', methods: ['GET'])]
    public function show(Partnership $partnership): Response
    {
        return $this->render('service/partnership/show.html.twig', [
            'partnership' => $partnership,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partnership_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partnership $partnership, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(PartnershipType::class, $partnership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_partnership_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('service/partnership/edit.html.twig', [
            'partnership' => $partnership,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partnership_delete', methods: ['POST'])]
    public function delete(Request $request, Partnership $partnership, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partnership->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($partnership);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partnership_index', [], Response::HTTP_SEE_OTHER);
    }
} 