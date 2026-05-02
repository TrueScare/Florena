<?php

namespace App\Controller;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Repository\LocationsRepository;
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
}
