<?php

namespace App\Controller\Admin;

use App\Controller\PartnershipController;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/admin/partnership')]
class AdminPartnershipController extends AbstractController
{
    #[Route('/', name: 'admin_partnership_index', methods: ['GET'])]
    public function index(): Response
    {
        return $this->redirectToRoute('app_partnership_index');
    }

    #[Route('/new', name: 'admin_partnership_new', methods: ['GET'])]
    public function new(): Response
    {
        return $this->redirectToRoute('app_partnership_new');
    }

    #[Route('/{id}', name: 'admin_partnership_show', methods: ['GET'])]
    public function show(int $id): Response
    {
        return $this->redirectToRoute('app_partnership_show', ['id' => $id]);
    }

    #[Route('/{id}/edit', name: 'admin_partnership_edit', methods: ['GET'])]
    public function edit(int $id): Response
    {
        return $this->redirectToRoute('app_partnership_edit', ['id' => $id]);
    }
} 