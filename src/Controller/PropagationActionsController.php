<?php

namespace App\Controller;

use App\Entity\Plants;
use App\Entity\PropagationActions;
use App\Enum\Status;
use App\Form\PropagationActionsType;
use App\Service\NotificationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/propagation_actions')]
#[IsGranted("IS_AUTHENTICATED")]
final class PropagationActionsController extends AbstractController
{
    #[Route('/new', name: 'app_propagation_actions_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $propagationAction = new PropagationActions();
        $form = $this->createForm(PropagationActionsType::class, $propagationAction, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($propagationAction);
            $entityManager->flush();

            return $this->redirectToRoute('app_plants_show', ['id' => $propagationAction->getPlant()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('propagation_actions/new.html.twig', [
            'propagation_action' => $propagationAction,
            'form' => $form,
            'plant' => null,
        ]);
    }

    #[Route('/new/plant/{id}', name: 'app_propagation_actions_new_for_plant', methods: ['GET', 'POST'])]
    public function newForPlant(Request $request, Plants $plant, EntityManagerInterface $entityManager): Response
    {
        if ($plant->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $propagationAction = new PropagationActions();
        $propagationAction->setPlant($plant);

        $form = $this->createForm(PropagationActionsType::class, $propagationAction, [
            'user' => $this->getUser(),
            'include_plant_field' => false,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($propagationAction);
            $entityManager->flush();

            return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('propagation_actions/new.html.twig', [
            'propagation_action' => $propagationAction,
            'form' => $form,
            'plant' => $plant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_propagation_actions_edit', methods: ['GET', 'POST'])]
    public function edit(
        Request $request,
        PropagationActions $propagationAction,
        EntityManagerInterface $entityManager,
        NotificationService $notificationService,
    ): Response
    {
        if ($propagationAction->getPlant()->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PropagationActionsType::class, $propagationAction,[
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($propagationAction->getStatus() === Status::finished) {
                $notificationService->deactivateNotificationsForPropagationAction($propagationAction);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_plants_show', ['id' => $propagationAction->getPlant()->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('propagation_actions/edit.html.twig', [
            'propagation_action' => $propagationAction,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_propagation_actions_delete', methods: ['POST'])]
    public function delete(Request $request, PropagationActions $propagationAction, EntityManagerInterface $entityManager): Response
    {
        if ($propagationAction->getPlant()->getUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $plantId = $propagationAction->getPlant()->getId();

        if ($this->isCsrfTokenValid('delete' . $propagationAction->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($propagationAction);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plants_show', ['id' => $plantId], Response::HTTP_SEE_OTHER);
    }
}
