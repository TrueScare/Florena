<?php

namespace App\Tests\Controller;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LocationsControllerSecurityTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Locations> */
    private EntityRepository $locationRepository;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    private User $owner;
    private User $attacker;
    private Locations $location;

    private string $path = '/locations/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->locationRepository = $this->manager->getRepository(Locations::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach ($this->locationRepository->findAll() as $l) {
            $this->manager->remove($l);
        }
        $this->manager->flush();

        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);

        $this->location = $this->createLocation($this->owner);
        $this->manager->persist($this->location);
        $this->manager->flush();
    }

    protected function tearDown(): void
    {
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach ($this->locationRepository->findAll() as $l) {
            $this->manager->remove($l);
        }
        $this->manager->flush();

        parent::tearDown();
    }
    public function testShowRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->location->getId());

        self::assertResponseRedirects('/locations');
    }

    public function testShowAllowedForOwner(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->location->getId());

        self::assertResponseIsSuccessful();
    }
    public function testEditGetRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->location->getId() . '/edit');

        self::assertResponseRedirects('/locations');
    }

    public function testEditPostRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->location->getId() . '/edit');

        self::assertResponseRedirects('/locations');

        $this->manager->refresh($this->location);
        self::assertSame('Testlocation', $this->location->getName());
    }
    public function testDeleteRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->location->getId(), [
            '_token' => 'invalid',
        ]);

        self::assertResponseRedirects('/locations');
        self::assertSame(1, $this->locationRepository->count([]));
    }
    public function testDeleteWithInvalidCsrfTokenDoesNotDelete(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('POST', $this->path . $this->location->getId(), [
            '_token' => 'completely_wrong_token',
        ]);

        self::assertResponseRedirects('/locations');
        self::assertSame(1, $this->locationRepository->count([]));
    }

    public function testDeleteWithAssignedPlantRedirectsToEdit(): void
    {
        $plant = $this->createPlant($this->owner, $this->location);
        $this->manager->persist($plant);
        $this->manager->flush();

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->location->getId());
        $this->client->submitForm('Löschen');

        // Must redirect to edit, not to index
        self::assertResponseRedirects('/locations/' . $this->location->getId() . '/edit');
        self::assertSame(1, $this->locationRepository->count([]));
    }

    private function createLocation(User $user): Locations
    {
        $location = new Locations();
        $location->setName('Testlocation');
        $location->setDescription('Desc');
        $location->setLightCondition(LightRequirement::bright);
        $location->setTemperatureLevel(TemperatureRequirement::normal);
        $location->setHumidityLevel(HumidityRequirement::medium);
        $location->setUser($user);

        return $location;
    }

    private function createPlant(User $user, Locations $location): Plants
    {
        $plant = new Plants();
        $plant->setName('PlantInLocation');
        $plant->setLightRequirement(LightRequirement::bright);
        $plant->setTemperatureRequirement(TemperatureRequirement::normal);
        $plant->setHumidityRequirement(HumidityRequirement::medium);
        $plant->setLastWateredAt(new \DateTimeImmutable());
        $plant->setLastFertilizedAt(new \DateTimeImmutable());
        $plant->setLastRepottedAt(new \DateTimeImmutable());
        $plant->setToxicForHumans(false);
        $plant->setToxicForAnimals(false);
        $plant->setUser($user);
        $plant->setLocation($location);

        return $plant;
    }
}
