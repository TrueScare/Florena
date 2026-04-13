<?php

namespace App\Controller;

use App\Entity\Plants;
use App\Form\PlantsType;
use App\Repository\PlantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/plants')]
#[IsGranted("IS_AUTHENTICATED")]
final class PlantsController extends AbstractController
{
    #[Route(name: 'app_plants_index', methods: ['GET'])]
    public function index(PlantsRepository $plantsRepository): Response
    {
        return $this->render('plants/index.html.twig', [
            'plants' => $plantsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_plants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, string $uploadsPath): Response
    {
        $plant = new Plants();
        $form = $this->createForm(PlantsType::class, $plant, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        $plant->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();
            if ($uploadedFile) {
                $destination = $uploadsPath . '/plant_images';
                $newFileName = uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move($destination, $newFileName);

                $plant->setPhotoPath($newFileName);
            }

            $entityManager->persist($plant);
            $entityManager->flush();

            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plants/new.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plants_show', methods: ['GET'])]
    public function show(Plants $plant): Response
    {
        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plants/show.html.twig', [
            'plant' => $plant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plants $plant, EntityManagerInterface $entityManager, string $uploadsPath): Response
    {
        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $currentImagePath = $uploadsPath . '/plant_images/' . $plant->getPhotoPath();

        $form = $this->createForm(PlantsType::class, $plant, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                if ($currentImagePath) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($currentImagePath);
                }
                $destination = $uploadsPath . '/plant_images';
                $newFileName = uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move($destination, $newFileName);

                $plant->setPhotoPath($newFileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plants/edit.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plants_delete', methods: ['POST'])]
    public function delete(Request $request, Plants $plant, EntityManagerInterface $entityManager): Response
    {
        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $plant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
    }
}
