<?php

namespace App\Controller;

use App\Entity\Categorie;
use App\Form\CategoryType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CategoryController extends AbstractController
{
    #[Route('/category/new', name: 'app_category_new', methods: ['GET', 'POST'])]
    #[Route('/event/new', name: 'app_event_new')]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $category = new Categorie();
    
        $form = $this->createForm(CategoryType::class, $category);
    
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            $entityManager->persist($category);
            $entityManager->flush();
    
            // Redirect to the category details page with the event ID
            return $this->redirectToRoute('app_show_all_catsBack', ['id' => $category->getId()]);
        }
    
        return $this->render('backOFF/addCategory.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_show', methods: ['GET'])]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Categorie::class)->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        
        return $this->render('category/show.html.twig', [
            'category' => $category
        ]);
    }

    #[Route('/category/{id}/edit', name: 'app_category_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Categorie::class)->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        
        $form = $this->createForm(CategoryType::class, $category);
        $form->handleRequest($request);
        
        if ($form->isSubmitted() && $form->isValid()) {
            $em->flush();
            return $this->redirectToRoute('app_show_all_catsBack', ['id' => $category->getId()]);
        }
        
        return $this->render('backOFF/editCategory.html.twig', [
            'form' => $form->createView(),
            'category' => $category
        ]);
    }

    #[Route('/category/{id}', name: 'app_category_delete', methods: ['POST'])]
    public function delete(Request $request, int $id, EntityManagerInterface $em): Response
    {
        $category = $em->getRepository(Categorie::class)->find($id);
        
        if (!$category) {
            throw $this->createNotFoundException('Category not found');
        }
        
        if ($this->isCsrfTokenValid('delete'.$category->getId(), $request->request->get('_token'))) {
            $em->remove($category);
            $em->flush();
        }
        
        return $this->redirectToRoute('app_category_list');
    }

    #[Route('/categories', name: 'app_category_list', methods: ['GET'])]
    public function list(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        
        return $this->render('category/list.html.twig', [
            'categories' => $categories
        ]);
    }

    #[Route('/catsBack', name: 'app_show_all_catsBack', methods: ['GET'])]
    public function showAllBack(EntityManagerInterface $em): Response
    {
        $categories = $em->getRepository(Categorie::class)->findAll();
        
        return $this->render('backOFF/categoryListBack.html.twig', [
            'categories' => $categories
        ]);
    }
    
}