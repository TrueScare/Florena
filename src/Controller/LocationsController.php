<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Form\LocationsType;
use App\Repository\LocationsRepository;
use App\Service\Fitness\FitnessService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/locations')]
#[IsGranted('IS_AUTHENTICATED')]
final class LocationsController extends AbstractController
{
    #[Route(name: 'app_locations_index', methods: ['GET'])]
    public function index(LocationsRepository $locationsRepository): Response
    {
        return $this->render('locations/index.html.twig', [
            'locations' => $locationsRepository->findAllByUser($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_locations_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $location = new Locations();
        $form = $this->createForm(LocationsType::class, $location);
        $form->handleRequest($request);

        $location->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($location);
            $entityManager->flush();

            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('locations/new.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_locations_show', methods: ['GET'])]
    public function show(Locations $location, FitnessService $fitnessService): Response
    {
        // make sure the user that is trying to perform the action is also the owner of the resource
        if($this->getUser() !== $location->getUser()){
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        $plantFitnessStatuses = [];

        foreach ($location->getPlants() as $plant) {
            $plantFitnessStatuses[$plant->getId()] = $fitnessService
                ->checkFitForPlantInLocation(
                    $plant->getLightRequirement(),
                    $plant->getTemperatureRequirement(),
                    $plant->getHumidityRequirement(),
                    $location
                )
                ->getStatus()
                ->value;
        }

        return $this->render('locations/show.html.twig', [
            'location' => $location,
            'plantFitnessStatuses' => $plantFitnessStatuses,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_locations_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Locations $location, EntityManagerInterface $entityManager): Response
    {
        // make sure the user that is trying to perform the action is also the owner of the resource
        if($this->getUser() !== $location->getUser()){
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(LocationsType::class, $location);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('locations/edit.html.twig', [
            'location' => $location,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_locations_delete', methods: ['POST'])]
    public function delete(Request $request, Locations $location, EntityManagerInterface $entityManager): Response
    {
        // make sure the user that is trying to perform the action is also the owner of the resource
        if($this->getUser() !== $location->getUser()){
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        // the location still has connection to plants
        if($location->getPlants()->count() > 0 || $location->getWishlistPlants()->count() > 0){
            return $this->redirectToRoute('app_locations_edit', ['id' => $location->getId()], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete'.$location->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($location);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
    }
}
