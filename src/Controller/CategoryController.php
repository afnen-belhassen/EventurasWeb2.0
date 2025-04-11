<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Repository\CategorieRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/api/category')]
class CategoryController extends AbstractController
{
    #[Route('/', name: 'app_category_index', methods: ['GET'])]
    public function index(CategorieRepository $categorieRepository): JsonResponse
    {
        $categories = $categorieRepository->findAll();
        $data = [];
        
        foreach ($categories as $category) {
            $data[] = [
                'id' => $category->getCategoryId(),
                'name' => $category->getName(),
            ];
        }
        
        return $this->json($data);
    }

    #[Route('/new', name: 'app_category_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $category = new Categorie();
        $category->setName($data['name']);
        
        $entityManager->persist($category);
        $entityManager->flush();
        
        return $this->json([
            'id' => $category->getCategoryId(),
            'name' => $category->getName(),
        ], Response::HTTP_CREATED);
    }

    #[Route('/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(Categorie $category): JsonResponse
    {
        return $this->json([
            'id' => $category->getCategoryId(),
            'name' => $category->getName(),
        ]);
    }

    #[Route('/{id}/edit', name: 'app_category_edit', methods: ['PUT'])]
    public function edit(Request $request, Categorie $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        
        $category->setName($data['name']);
        
        $entityManager->flush();
        
        return $this->json([
            'id' => $category->getCategoryId(),
            'name' => $category->getName(),
        ]);
    }

    #[Route('/{id}', name: 'app_category_delete', methods: ['DELETE'])]
    public function delete(Categorie $category, EntityManagerInterface $entityManager): JsonResponse
    {
        $entityManager->remove($category);
        $entityManager->flush();
        
        return $this->json(null, Response::HTTP_NO_CONTENT);
    }
} 