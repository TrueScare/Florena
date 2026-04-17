<?php

namespace App\DataFixtures;

use App\Entity\Locations;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class LocationsFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $location = new Locations();
            $location->setName('My Title-'. $i);
            $location->setDescription('My Description');
            $location->setLightCondition(LightRequirement::bright);
            $location->setTemperatureLevel(TemperatureRequirement::normal);
            $location->setHumidityLevel(HumidityRequirement::medium);
            $location->setUser($this->getReference(UserFixture::TEST_USER, User::class));

            $manager->persist($location);
        }
        $manager->flush();
    }

    public function getDependencies(): array
    {
        return [
            UserFixture::class
        ];
    }

    public static function getGroups(): array
    {
        return [
            'test_data'
        ];
    }


}
