<?php

namespace App\Controller\Admin;

use App\Entity\Partner;
use App\Form\PartnerType;
use App\Repository\PartnerRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use App\Entity\Partnership;
use App\Form\PartnershipType;

#[Route('/partner')]
class PartnerController extends AbstractController
{
    #[Route('/', name: 'app_partner_index', methods: ['GET'])]
    public function index(PartnerRepository $partnerRepository): Response
    {
        return $this->render('service/partner/index.html.twig', [
            'partners' => $partnerRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_partner_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $partner = new Partner();
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('partners_directory'),
                        $newFilename
                    );
                    $partner->setImagePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }

            $entityManager->persist($partner);
            $entityManager->flush();

            return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partner/new.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partner_show', methods: ['GET'])]
    public function show(Partner $partner): Response
    {
        return $this->render('partner/show.html.twig', [
            'partner' => $partner,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_partner_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Partner $partner, EntityManagerInterface $entityManager, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(PartnerType::class, $partner);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Handle image upload
            $imageFile = $form->get('imagePath')->getData();
            if ($imageFile instanceof UploadedFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$imageFile->guessExtension();

                try {
                    $imageFile->move(
                        $this->getParameter('partners_directory'),
                        $newFilename
                    );
                    
                    // Remove old image if exists
                    if ($partner->getImagePath()) {
                        $oldImagePath = $this->getParameter('partners_directory').'/'.$partner->getImagePath();
                        if (file_exists($oldImagePath)) {
                            unlink($oldImagePath);
                        }
                    }
                    
                    $partner->setImagePath($newFilename);
                } catch (FileException $e) {
                    $this->addFlash('error', 'An error occurred while uploading the image.');
                }
            }

            $partner->setUpdatedAt(new \DateTimeImmutable());
            $entityManager->flush();

            return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('partner/edit.html.twig', [
            'partner' => $partner,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_partner_delete', methods: ['POST'])]
    public function delete(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$partner->getId(), $request->request->get('_token'))) {
            // Remove image file if exists
            if ($partner->getImagePath()) {
                $imagePath = $this->getParameter('partners_directory').'/'.$partner->getImagePath();
                if (file_exists($imagePath)) {
                    unlink($imagePath);
                }
            }
            
            $entityManager->remove($partner);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_partner_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/generate-contract', name: 'app_partner_generate_contract', methods: ['GET', 'POST'])]
    public function generateContract(Request $request, Partner $partner, EntityManagerInterface $entityManager): Response
    {
        $partnership = new Partnership();
        $partnership->setPartnerId($partner);
        $partnership->setName($partner->getName() ?? '');
        $partnership->setStatus('pending');
        $partnership->setCreatedAt(new \DateTime());
        $partnership->setContracttype('Standard');
        $partnership->setDescription('Partnership with ' . $partner->getName());

        $form = $this->createForm(PartnershipType::class, $partnership);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($partnership);
            $entityManager->flush();

            return $this->redirectToRoute('app_partnership_index');
        }

        return $this->render('service/partnership/new.html.twig', [
            'partnership' => $partnership,
            'form' => $form->createView(),
        ]);
    }
} 