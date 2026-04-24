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
        $fitnessInformation = new FitnessInformation($location, Locations::class);

        $count = 0;

        // make sure we know where the mismatches come from
        if ($light->matches($location->getLightCondition())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$light::class => [$light->value => $location->getLightCondition()]]);
        }

        if ($humidity->matches($location->getHumidityLevel())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$humidity::class => [$humidity->value => $location->getHumidityLevel()]]);
        }

        if ($temperature->matches($location->getTemperatureLevel())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$temperature::class => [$temperature->value => $location->getTemperatureLevel()]]);
        }

        match ($count) {
            0 => $fitnessInformation->setStatus(FitnessStatus::none),
            1, 2 => $fitnessInformation->setStatus(FitnessStatus::partly),
            3 => $fitnessInformation->setStatus(FitnessStatus::perfect),
        };

        return $fitnessInformation;
    }

    public function checkFitForLocationInPlant(
        LightRequirement       $light,
        TemperatureRequirement $temperature,
        HumidityRequirement    $humidity,
        Plants                 $plant
    )
    {
        $fitnessInformation = new FitnessInformation($plant, Plants::class);

        $count = 0;

        // make sure we know where the mismatches come from
        if ($light->matches($plant->getLightRequirement())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$light->value => $plant->getLightRequirement()]);
        }

        if ($humidity->matches($plant->getHumidityRequirement())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$humidity->value => $plant->getHumidityRequirement()]);
        }

        if ($temperature->matches($plant->getTemperatureRequirement())) {
            $count++;
        } else {
            $fitnessInformation->addMissmatch([$temperature->value => $plant->getTemperatureRequirement()]);
        }

        match ($count) {
            0 => $fitnessInformation->setStatus(FitnessStatus::none),
            1, 2 => $fitnessInformation->setStatus(FitnessStatus::partly),
            3 => $fitnessInformation->setStatus(FitnessStatus::perfect),
        };

        return $fitnessInformation;
    }
}
