<?php

namespace App\DataFixtures;

use App\Entity\Locations;
use App\Entity\User;
use App\Entity\WishlistPlants;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Persistence\ObjectManager;

class WishlistPlantFixture extends Fixture implements DependentFixtureInterface, FixtureGroupInterface
{
    public function load(ObjectManager $manager): void
    {
        for ($i = 0; $i < 50; $i++) {
            $location = new WishlistPlants()
            ->setName('My Title-'. $i)
            ->setDescription('My Description')
            ->setUser($this->getReference(UserFixture::TEST_USER, User::class));

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
