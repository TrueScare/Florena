<?php

namespace App\Tests\API;

use App\Entity\CareHistory;
use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\CareType;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PointAPITest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $user;
    private User $otherUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        $userRepo = $this->em->getRepository(User::class);
        $this->user = $userRepo->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $userRepo->findOneBy(['username' => 'TestuserNoRef']);

        foreach ($this->em->getRepository(Notifications::class)->findAll() as $n) {
            $this->em->remove($n);
        }
        foreach ($this->em->getRepository(CareHistory::class)->findAll() as $h) {
            $this->em->remove($h);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        foreach ($this->em->getRepository(Notifications::class)->findAll() as $n) {
            $this->em->remove($n);
        }
        foreach ($this->em->getRepository(CareHistory::class)->findAll() as $h) {
            $this->em->remove($h);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();
    }

    public function testRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/api/points/user/' . $this->user->getId());
        self::assertResponseRedirects('/login');
    }

    public function testReturnsScoreAndPlacementForUserWithNoHistory(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/points/user/' . $this->user->getId());

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('score', $data);
        self::assertArrayHasKey('placement', $data);
        self::assertSame(0, $data['score']);
        self::assertIsInt($data['placement']);
    }

    public function testScoreIncreasesAfterCompletingOwnTask(): void
    {
        $plant = $this->createPlant($this->user);
        $this->em->persist($plant);
        $this->em->flush();

        // Mark one task as done via the API
        $careTask = $this->em->getRepository(\App\Entity\CareTask::class)
            ->findOneBy(['plant' => $plant]);

        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/care_task/' . $careTask->getId());
        self::assertResponseIsSuccessful();

        // Now check the points API
        $this->client->request('GET', '/api/points/user/' . $this->user->getId());
        $data = json_decode($this->client->getResponse()->getContent(), true);

        self::assertGreaterThan(0, $data['score']);
        self::assertSame(\App\Service\PointService::BASE_POINTS, $data['score']);
    }

    public function testOtherUserCanQueryAnyUserPoints(): void
    {
        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', '/api/points/user/' . $this->user->getId());

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('score', $data);
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('PointAPITestPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Pointicus testicus');
        $plant->setLightRequirement(LightRequirement::bright);
        $plant->setTemperatureRequirement(TemperatureRequirement::normal);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setSoilType('Erde');
        $plant->setPotSize('10 cm');
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
