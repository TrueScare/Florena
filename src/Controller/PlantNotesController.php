<?php

namespace App\Controller;

use App\Entity\PlantNotes;
use App\Entity\Plants;
use App\Form\PlantNotesType;
use App\Repository\PlantNotesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bridge\Doctrine\Attribute\MapEntity;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/plants/{id:plant}/notes',)]
final class PlantNotesController extends AbstractController
{
    #[Route('/new', name: 'app_plant_notes_new', methods: ['GET', 'POST'])]
    public function new(Plants $plant, Request $request, EntityManagerInterface $entityManager): Response
    {
        $plantNote = new PlantNotes()
            ->setPlant($plant);
        $form = $this->createForm(PlantNotesType::class, $plantNote);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($plantNote);
            $entityManager->flush();

            return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plant_notes/new.html.twig', [
            'plant' => $plant,
            'plant_note' => $plantNote,
            'form' => $form,
        ]);
    }

    #[Route('/{note}/edit', name: 'app_plant_notes_edit', methods: ['GET', 'POST'])]
    public function edit(Plants $plant, Request $request, #[MapEntity(id: 'note')]PlantNotes $plantNote, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getId() !== $plant->getUser()->getId()) {
            return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(PlantNotesType::class, $plantNote);
        $form->handleRequest($request);
        $plantNote->setUpdatedAt(new \DateTimeImmutable());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plant_notes/edit.html.twig', [
            'plant' => $plant,
            'plant_note' => $plantNote,
            'form' => $form,
        ]);
    }

    #[Route('/{note}', name: 'app_plant_notes_delete', methods: ['POST'])]
    public function delete(Plants $plant, Request $request, #[MapEntity(id: 'note')]PlantNotes $plantNote, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser()->getId() !== $plant->getUser()->getId()) {
            return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
        }
        if ($this->isCsrfTokenValid('delete' . $plantNote->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plantNote);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plants_show', ['id' => $plant->getId()], Response::HTTP_SEE_OTHER);
    }
}
