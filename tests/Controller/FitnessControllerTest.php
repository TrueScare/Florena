<?php

namespace App\Tests\Controller;

use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FitnessControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $owner;
    private User $otherUser;
    private Plants $plant;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $userRepo = $this->em->getRepository(User::class);
        $this->owner = $userRepo->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $userRepo->findOneBy(['username' => 'TestuserNoRef']);

        foreach ($this->em->getRepository(Notifications::class)->findAll() as $n) {
            $this->em->remove($n);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();

        $this->plant = $this->createPlant($this->owner);
        $this->em->persist($this->plant);
        $this->em->flush();
    }

    public function testPlantFitnessRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/fitness/plant/' . $this->plant->getId());
        self::assertResponseRedirects('/login');
    }

    public function testPlantFitnessRendersForOwner(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', '/fitness/plant/' . $this->plant->getId());
        self::assertResponseIsSuccessful();
    }

    public function testPlantFitnessRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', '/fitness/plant/' . $this->plant->getId());
        self::assertResponseRedirects('/plants');
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('FitnessTestPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Plantus fitnessius');
        $plant->setLightRequirement(LightRequirement::bright);
        $plant->setTemperatureRequirement(TemperatureRequirement::normal);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setSoilType('Erde');
        $plant->setPotSize('12 cm');
        $plant->setLastWateredAt(new \DateTimeImmutable());
        $plant->setLastFertilizedAt(new \DateTimeImmutable());
        $plant->setLastRepottedAt(new \DateTimeImmutable());
        $plant->setWateringIntervalDays(7);
        $plant->setFertilizingIntervalDays(30);
        $plant->setRepottingIntervalDays(365);
        $plant->setToxicForHumans(false);
        $plant->setToxicForAnimals(false);
        $plant->setUser($user);
        return $plant;
    }

}
