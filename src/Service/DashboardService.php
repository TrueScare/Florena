<?php

namespace App\Service;

use App\Entity\User;
use App\Repository\CareTaskRepository;
use App\Repository\LocationsRepository;
use App\Repository\PlantsRepository;

final class DashboardService
{
    private const int MAX_UPCOMING_TASKS = 3;
    private const int MAX_PLANTS = 4;
    private const int MAX_LOCATIONS = 3;
    private const string FALLBACK_PLANT_IMAGE =
    'https://images.unsplash.com/photo-1512428813834-c702c7702b78?auto=format&fit=crop&w=400&q=80';

    public function __construct(
        private PlantsRepository $plantsRepository,
        private CareTaskRepository $careTaskRepository,
        private LocationsRepository $locationsRepository,
    ) {
    }

    public function getDashboardData(User $user): array
    {
        $tasks = $this->careTaskRepository->findAllByUser($user);
        $plants = $this->plantsRepository->findAllByUser($user);
        $locations = $this->locationsRepository->findAllByUser($user);

        usort(
            $tasks,
            fn($left, $right) => $left->getDueDate() <=> $right->getDueDate()
        );

        $activePlants = array_values(array_filter(
            $plants,
            fn($plant) => null === $plant->getDiedAt()
        ));

        usort(
            $activePlants,
            fn($left, $right) => ($right->getStressScore() ?? 100) <=> ($left->getStressScore() ?? 100)
        );

        return [
            'dashboardGreetingName' => $user->getDisplayname() ?: $user->getUsername(),
            'upcomingTasks' => array_map(
                fn($task) => $this->mapTask($task),
                array_slice($tasks, 0, self::MAX_UPCOMING_TASKS)
            ),
            'plants' => array_map(
                fn($plant) => $this->mapPlant($plant),
                array_slice($activePlants, 0, self::MAX_PLANTS)
            ),
            'locations' => array_map(
                fn($location) => $this->mapLocation($location),
                array_slice($locations, 0, self::MAX_LOCATIONS)
            ),
            'locationCount' => count($locations),
        ];
    }

    private function mapTask(object $task): array
    {
        $type = $task->getTaskType()?->value ?? '';

        return [
            'type' => ucfirst($type),
            'plant' => $task->getPlant()?->getName() ?? 'Unbekannte Pflanze',
            'dueDate' => $task->getDueDate(),
        ];
    }

    private function mapPlant(object $plant): array
    {
        $stressScore = $plant->getStressScore() ?? 100;

        return [
            'id' => $plant->getId(),
            'name' => $plant->getName() ?? 'Unbenannte Pflanze',
            'location' => $plant->getLocation()?->getName() ?? 'Kein Standort',
            'stressLevel' => $stressScore,
            'imageUrl' => $plant->getPhotoPath() ? $plant->getFullPhotoPath() : self::FALLBACK_PLANT_IMAGE,
            'imageAlt' => $plant->getName() ?? 'Pflanzenbild',
        ];
    }

    private function mapLocation(object $location): array
    {
        return [
            'name' => $location->getName() ?? 'Unbenannter Standort',
            'description' => $location->getDescription() ?? ''
        ];
    }
}
