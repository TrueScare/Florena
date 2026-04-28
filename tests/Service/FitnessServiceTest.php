<?php

namespace App\Tests\Service;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Enum\FitnessStatus;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Service\Fitness\FitnessService;
use PHPUnit\Framework\TestCase;

final class FitnessServiceTest extends TestCase
{
    private FitnessService $service;

    protected function setUp(): void
    {
        $this->service = new FitnessService();
    }
    public function testPerfectFitWhenAllRequirementsMatch(): void
    {
        $location = $this->buildLocation(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::medium);
        $result = $this->service->checkFitForPlantInLocation(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::medium, $location);

        self::assertSame(FitnessStatus::perfect, $result->getStatus());
        self::assertEmpty($result->getMissmatches());
    }

    public function testPartlyFitWhenTwoRequirementsMatch(): void
    {
        $location = $this->buildLocation(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::low);
        $result = $this->service->checkFitForPlantInLocation(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::medium, $location);

        self::assertSame(FitnessStatus::partly, $result->getStatus());
        self::assertCount(1, $result->getMissmatches());
    }

    public function testNoFitWhenNoRequirementsMatch(): void
    {
        $location = $this->buildLocation(LightRequirement::shady, TemperatureRequirement::warm, HumidityRequirement::low);
        $result = $this->service->checkFitForPlantInLocation(LightRequirement::bright, TemperatureRequirement::cool, HumidityRequirement::medium, $location);

        self::assertSame(FitnessStatus::none, $result->getStatus());
        self::assertCount(3, $result->getMissmatches());
    }

    public function testPerfectFitForLocationInPlantWhenAllMatch(): void
    {
        $plant = $this->buildPlant(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::medium);
        $result = $this->service->checkFitForLocationInPlant(LightRequirement::bright, TemperatureRequirement::normal, HumidityRequirement::medium, $plant);

        self::assertSame(FitnessStatus::perfect, $result->getStatus());
        self::assertEmpty($result->getMissmatches());
    }

    public function testNoFitForLocationInPlantWhenNoneMatch(): void
    {
        $plant = $this->buildPlant(LightRequirement::shady, TemperatureRequirement::warm, HumidityRequirement::low);
        $result = $this->service->checkFitForLocationInPlant(LightRequirement::bright, TemperatureRequirement::cool, HumidityRequirement::medium, $plant);

        self::assertSame(FitnessStatus::none, $result->getStatus());
        self::assertCount(3, $result->getMissmatches());
    }

    public function testPartlyFitForLocationInPlantWhenOneMatches(): void
    {
        $plant = $this->buildPlant(LightRequirement::bright, TemperatureRequirement::warm, HumidityRequirement::low);
        $result = $this->service->checkFitForLocationInPlant(LightRequirement::bright, TemperatureRequirement::cool, HumidityRequirement::medium, $plant);

        self::assertSame(FitnessStatus::partly, $result->getStatus());
        self::assertCount(2, $result->getMissmatches());
    }

    private function buildLocation(LightRequirement $l, TemperatureRequirement $t, HumidityRequirement $h): Locations
    {
        $loc = new Locations();
        $loc->setName('Test');
        $loc->setLightCondition($l);
        $loc->setTemperatureLevel($t);
        $loc->setHumidityLevel($h);
        return $loc;
    }

    private function buildPlant(LightRequirement $l, TemperatureRequirement $t, HumidityRequirement $h): Plants
    {
        $plant = new Plants();
        $plant->setName('Test');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Test');
        $plant->setLightRequirement($l);
        $plant->setTemperatureRequirement($t);
        $plant->setHumidityRequirement($h);
        $plant->setSoilType('Erde');
        $plant->setPotSize('10');
        $plant->setLastWateredAt(new \DateTimeImmutable());
        $plant->setLastFertilizedAt(new \DateTimeImmutable());
        $plant->setLastRepottedAt(new \DateTimeImmutable());
        $plant->setWateringIntervalDays(7);
        $plant->setFertilizingIntervalDays(30);
        $plant->setRepottingIntervalDays(365);
        $plant->setToxicForHumans(false);
        $plant->setToxicForAnimals(false);
        return $plant;
    }
}
