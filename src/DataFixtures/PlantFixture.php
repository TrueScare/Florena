<?php

namespace App\DataFixtures;

use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class PlantFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $plant = new Plants();
            $plant->setName("Testplant-" . $i);
            $plant->setDescription("Description");
            $plant->setBotanicalName("BotanicalName");
            $plant->setLightRequirement(LightRequirement::halfshady);
            $plant->setTemperatureRequirement(TemperatureRequirement::cool);
            $plant->setHumidityRequirement(HumidityRequirement::medium);
            $plant->setSoilType("TestSoilType");
            $plant->setPotSize("20 cm");
            $plant->setLastFertilizedAt(new \DateTimeImmutable()->modify("-1 month"));
            $plant->setLastRepottedAt(new \DateTimeImmutable()->modify("-1 year"));
            $plant->setLastWateredAt(new \DateTimeImmutable()->modify("-3 days"));
            $plant->setWateringIntervalDays(7);
            $plant->setFertilizingIntervalDays(30);
            $plant->setRepottingIntervalDays(385);
            $plant->setToxicForAnimals(false);
            $plant->setToxicForHumans(false);
            $plant->setPurchaseDate(new \DateTimeImmutable());
            $plant->setStressScore(100);
            $plant->setUser($this->getReference(UserFixture::TEST_USER, User::class));

            $manager->persist($plant);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class,
        ];
    }

    public static function getGroups(): array
    {
        return [
            'test_data'
        ];
    }
}
