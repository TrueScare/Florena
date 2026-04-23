<?php

namespace App\Controller\API;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Enum\FitnessStatus;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Repository\LocationsRepository;
use App\Repository\PlantsRepository;
use App\Service\Fitness\FitnessService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/fitness')]
#[IsGranted("IS_AUTHENTICATED")]
class FitnessController extends AbstractController
{
    public function __construct(
        private FitnessService      $fitnessService,
        private LocationsRepository $locationsRepository
    )
    {
    }

    #[Route('/location', name: 'fitness')]
    public function getLocation(Request $request): JsonResponse
    {
        $light = LightRequirement::tryFrom($request->query->get('light_requirement'));
        $temperature = TemperatureRequirement::tryFrom($request->query->get('temperature_requirement'));
        $humidity = HumidityRequirement::tryFrom($request->query->get('humidity_requirement'));

        if ($light == null || $temperature == null || $humidity == null) {
            return $this->json([], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

        $fitness = [];
        $locations = $this->locationsRepository->findAllByUser($this->getUser());
        /** @var Locations $location */
        foreach ($locations as $location) {
            $fitness[$this->fitnessService->checkFitForPlantInLocation(
                $light,
                $temperature,
                $humidity,
                $location
            )->getStatus()->value][$location->getId()] = $location;
        }

        return $this->json($fitness, Response::HTTP_OK, context: ['groups' => [
            'plant:ref', 'location:ref'
        ]]);
    }
}
