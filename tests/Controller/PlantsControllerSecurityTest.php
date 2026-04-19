<?php

namespace App\Tests\Controller;

use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PlantsControllerSecurityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    private User $owner;
    private User $attacker;
    private Plants $plant;

    private string $path = '/plants/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        // Clean up plants from previous runs
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        $this->manager->flush();

        // Fixture users: 'Testuser' is the owner, 'TestuserNoRef' is the attacker
        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);

        $this->plant = $this->createPlant($this->owner);
        $this->manager->persist($this->plant);
        $this->manager->flush();
    }
    public function testShowRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->plant->getId());

        self::assertResponseRedirects('/plants');
    }

    public function testShowAllowedForOwner(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->plant->getId());

        self::assertResponseIsSuccessful();
    }

    public function testEditGetRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->plant->getId() . '/edit');

        self::assertResponseRedirects('/plants');
    }

    public function testEditPostRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->plant->getId() . '/edit');

        self::assertResponseRedirects('/plants');

        // Plant name must be unchanged
        $this->manager->refresh($this->plant);
        self::assertSame('Testplant', $this->plant->getName());
    }

    public function testDeleteRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->plant->getId(), [
            '_token' => 'invalid',
        ]);

        self::assertResponseRedirects('/plants');

        // Plant must still exist
        self::assertSame(1, $this->plantRepository->count([]));
    }

    public function testDeleteWithInvalidCsrfTokenDoesNotDelete(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('POST', $this->path . $this->plant->getId(), [
            '_token' => 'completely_wrong_token',
        ]);

        self::assertResponseRedirects('/plants');
        self::assertSame(1, $this->plantRepository->count([]));
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('Testplant');
        $plant->setDescription('Desc');
        $plant->setBotanicalName('Botanicus testus');
        $plant->setLightRequirement(LightRequirement::halfshady);
        $plant->setTemperatureRequirement(TemperatureRequirement::cool);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setSoilType('Erde');
        $plant->setPotSize('15 cm');
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
