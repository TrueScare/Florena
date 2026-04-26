<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Repository\LocationsRepository;
use App\Repository\PlantsRepository;
use App\Service\Fitness\FitnessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/fitness')]
#[IsGranted("IS_AUTHENTICATED")]
class FitnessController extends AbstractController
{
    public function __construct(
        private FitnessService $fitnessService,
        private PlantsRepository $plantsRepository,
        private LocationsRepository $locationsRepository,
    )
    {
    }

    #[Route("/plant/{id}", name: "app_fitness_plant")]
    public function plantIndex(Plants $plant): Response
    {
        if($this->getUser() !== $plant->getUser()){
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $fitness = [];
        $locations = $this->locationsRepository->findAllByUser($this->getUser());
        /** @var Locations $location */
        foreach ($locations as $location) {
            $fitnessInformation = $this->fitnessService->checkFitForPlantInLocation(
                $plant->getLightRequirement(),
                $plant->getTemperatureRequirement(),
                $plant->getHumidityRequirement(),
                $location
            );
            $fitness[$fitnessInformation->getStatus()->value][$location->getId()] = $fitnessInformation;
        }

        return $this->render('fitness/plant.html.twig', [
            'fitnessInformation' => $fitness,
            'plant' => $plant,
        ]);
    }

    #[Route("/location/{id}", name: "app_fitness_location")]
    public function locationIndex(Locations $location): Response
    {
        if($this->getUser() !== $location->getUser()){
            return $this->redirectToRoute('app_locations_index', [], Response::HTTP_SEE_OTHER);
        }

        $locations = $this->locationsRepository->findAllByUser($this->getUser());

        $fitness = [];
        $plants = $this->plantsRepository->findAllByUserWithImperfectLocation($this->getUser());
        /** @var Plants $plant */
        foreach ($plants as $plant) {
            $fitnessInformation =$this->fitnessService->checkFitForLocationInPlant(
                $location->getLightCondition(),
                $location->getTemperatureLevel(),
                $location->getHumidityLevel(),
                $plant
            );
            $fitness[$fitnessInformation->getStatus()->value][$plant->getId()] = $fitnessInformation;
        }

        return $this->render('fitness/index.html.twig', [
            'fitnessInformation' => $fitness,
            'selected' => $location,
            'locations' => $locations,
        ]);
    }
}
