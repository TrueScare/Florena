<?php

namespace App\Service\Fitness;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Enum\FitnessStatus;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;

class FitnessService
{
    public function checkFitForPlantInLocation(
        LightRequirement       $light,
        TemperatureRequirement $temperature,
        HumidityRequirement    $humidity,
        Locations              $location
    ): FitnessInformation
    {
        $fitnessInformation = new FitnessInformation($location->getId());

        $count = 0;

        // make sure we know where the mismatches come from
        if ($light->matches($location->getLightCondition())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$light->value => $location->getLightCondition()]);
        }

        if ($humidity->matches($location->getHumidityLevel())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$humidity->value => $location->getHumidityLevel()]);
        }

        if ($temperature->matches($location->getTemperatureLevel())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$temperature->value => $location->getTemperatureLevel()]);
        }

        match ($count) {
            0 => $fitnessInformation->setStatus(FitnessStatus::none),
            1, 2 => $fitnessInformation->setStatus(FitnessStatus::partly),
            3 => $fitnessInformation->setStatus(FitnessStatus::perfect),
        };

        return $fitnessInformation;
    }
}
