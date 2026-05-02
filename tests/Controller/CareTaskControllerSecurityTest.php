<?php

namespace App\Tests\Controller;

use App\Entity\CareTask;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\CareType;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CareTaskControllerSecurityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<CareTask> */
    private EntityRepository $careTaskRepository;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    private User $owner;
    private User $attacker;
    private CareTask $careTask;

    private string $path = '/care_task/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->careTaskRepository = $this->manager->getRepository(CareTask::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        $this->manager->flush();

        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);

        // Create a plant with watering interval so a CareTask is auto-created via the listener
        $plant = $this->createPlant($this->owner);
        $this->manager->persist($plant);
        $this->manager->flush();

        $this->careTask = $this->careTaskRepository->findOneBy(['plant' => $plant]);
        self::assertNotNull($this->careTask, 'PlantsUpdateListener must have created a CareTask');
    }

    public function testIndexReturns200ForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);

        self::assertResponseIsSuccessful();
    }
    public function testIndexRedirectsWhenUnauthenticated(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);
        self::assertStringContainsString('/login', $this->client->getRequest()->getUri());
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('CareTaskTestPlant');
        $plant->setLightRequirement(LightRequirement::bright);
        $plant->setTemperatureRequirement(TemperatureRequirement::normal);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setLastWateredAt(new \DateTimeImmutable());
        $plant->setLastFertilizedAt(new \DateTimeImmutable());
        $plant->setLastRepottedAt(new \DateTimeImmutable());
        $plant->setWateringIntervalDays(7);
        $plant->setFertilizingIntervalDays(0);
        $plant->setRepottingIntervalDays(0);
        $plant->setToxicForHumans(false);
        $plant->setToxicForAnimals(false);
        $plant->setUser($user);

        return $plant;
    }
}
