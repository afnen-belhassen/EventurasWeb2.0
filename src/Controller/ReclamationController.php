<?php
namespace App\Controller;

use App\Entity\Reclamation;
use App\Entity\ReclamationAttachment;
use App\Entity\ReclamationConversation;
use App\Form\ReclamationType;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\JsonResponse;

final class ReclamationController extends AbstractController
{

    
    #[Route('/reclamsBack', name: 'app_show_all_reclamsBack')]
    public function showReclamsBack(EntityManagerInterface $entityManager, Request $request): Response
    {
        $reclamations = $entityManager->getRepository(Reclamation::class)->findAll();

        return $this->render('backOFF/reclamsBack.html.twig', [
            'reclamations' => $reclamations,
        ]);
    }





    #[Route('/reclamations', name: 'app_show_all_reclamations', methods: ['GET'])]
    public function showAll(EntityManagerInterface $em): Response
    {
        // 1) fetch existing reclamations
        $reclamations = $em->getRepository(Reclamation::class)->findAll();

        // 2) build the “create” form for the popup
        $newRecl = new Reclamation();
        $newRecl->setStatus('En attente');
        $form = $this->createForm(ReclamationType::class, $newRecl);

        // 3) render list + form
        return $this->render('reclamation/reclam.html.twig', [
            'reclamations' => $reclamations,
            'form'         => $form->createView(),
        ]);
    }

    #[Route('/reclamation/{id}/delete', name: 'app_reclamation_delete', methods: ['POST'])]
    public function delete(Request $request, Reclamation $reclamation, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('delete'.$reclamation->getId(), $request->request->get('_token'))) {
            $entityManager->remove($reclamation);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_show_all_reclamations');
    }
    #[Route('/reclamation/new', name: 'app_reclamation_new', methods: ['POST'])]
    public function new(Request $request, EntityManagerInterface $em): Response
    {
        $reclamation = new Reclamation();
        $reclamation->setStatus('En attente');
        // created_at is set in __construct of the entity

        $form = $this->createForm(ReclamationType::class, $reclamation);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // 1) persist the reclamation
            $em->persist($reclamation);

            // 2) handle uploaded files
            /** @var UploadedFile[] $files */
            $files = $form->get('attachments')->getData();
            foreach ($files as $file) {
                if (!$file) {
                    continue;
                }
                $origName    = pathinfo($file->getClientOriginalName(), PATHINFO_FILENAME);
                $safeName    = preg_replace('/[^a-z0-9_]+/i', '_', $origName);
                $newFilename = $safeName.'-'.uniqid().'.'.$file->guessExtension();

                try {
                    $file->move(
                        $this->getParameter('attachments_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // log or handle exception as needed
                }

                $attach = new ReclamationAttachment();
                $attach->setFilePath($newFilename);
                $attach->setReclamation($reclamation);
                $em->persist($attach);
            }

            // 3) flush all to database
            $em->flush();

            $this->addFlash('success', 'Réclamation créée avec succès');
        } else {
            $this->addFlash('error', 'Erreur lors de la création de la réclamation');
        }

        // always redirect back to the list
        return $this->redirectToRoute('app_show_all_reclamations');
    }

#[Route('/reclamation/{id}/edit', name: 'app_reclamation_edit', methods: ['POST'])]
public function edit(
    Reclamation $reclamation,
    Request $request,
    EntityManagerInterface $em
): Response {
    // 1) grab the entire POST payload
    $post = $request->request->all();

    // 2) extract your “reclamation” sub‑array (or fallback to empty array)
    $data = [];
    if (isset($post['reclamation']) && is_array($post['reclamation'])) {
        $data = $post['reclamation'];
    }

    // 3) apply updates, falling back to current values
    $reclamation
        ->setIdUser((int)($data['id_user']   ?? $reclamation->getIdUser()))
        ->setIdEvent(isset($data['id_event']) ? (int)$data['id_event'] : null)
        ->setDescription($data['description'] ?? $reclamation->getDescription())
        ->setSubject($data['subject']       ?? $reclamation->getSubject())
    ;

    $em->flush();
    $this->addFlash('success', 'Réclamation mise à jour !');

    return $this->redirectToRoute('app_show_all_reclamations');
}




#[Route('/reclamation/{id}/accept', name: 'reclamation_accept', methods: ['POST'])]
public function accept(Reclamation $reclamation, EntityManagerInterface $em): JsonResponse
{
    $reclamation->setStatus('En cours');

    $conversation = new ReclamationConversation();
    $conversation->setReclamation($reclamation);
    $conversation->setCreatedAt(new \DateTimeImmutable());
    $conversation->setStatus('active');

    $em->persist($conversation);
    $em->flush();

    return new JsonResponse(['success' => true]);
}

#[Route('/reclamation/{id}/refuse', name: 'reclamation_refuse', methods: ['POST'])]
public function refuse(Request $request, Reclamation $reclamation, EntityManagerInterface $em): JsonResponse
{
    $data = json_decode($request->getContent(), true);
    $reason = $data['reason'] ?? null;

    if (!$reason) {
        return new JsonResponse(['error' => 'Raison manquante.'], 400);
    }

    $reclamation->setStatus('Rejeté');
    $reclamation->setRefuseReason($reason);
    $em->flush();

    return new JsonResponse(['success' => true]);
    
}


#[Route('/reclamation/{id}/conversation', name: 'reclamation_conversation')]
public function showConversation(Reclamation $reclamation): Response
{
    return $this->render('reclamation/reclamConvo.html.twig', [
        'reclamation' => $reclamation,
    ]);
}



}
