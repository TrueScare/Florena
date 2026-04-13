<?php

namespace App\Tests\Controller;

use App\DataFixtures\UserFixture;
use App\Entity\Locations;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Bundle\SecurityBundle\Security;

final class LocationsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Locations> */
    private EntityRepository $locationRepository;
    private EntityRepository $userRepository;
    private string $path = '/locations/';

    private ?Locations $locations = null;
    private ?User $user = null;

    protected function setUp(): void
    {
        $this->client = LocationsControllerTest::createClient();
        $this->manager = LocationsControllerTest::getContainer()->get('doctrine')->getManager();
        $this->locationRepository = $this->manager->getRepository(Locations::class);

        foreach ($this->locationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->userRepository = $this->manager->getRepository(User::class);
        $this->user = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->client->loginUser($this->user);

        $this->locations = new Locations();
        $this->locations->setName('My Title');
        $this->locations->setDescription('My Description');
        $this->locations->setLightCondition(LightRequirement::bright);
        $this->locations->setTemperatureLevel(TemperatureRequirement::normal);
        $this->locations->setHumidityLevel(HumidityRequirement::medium);
        $this->locations->setUser($this->user);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach($this->locationRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }


    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Standorte');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));
        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Speichern', [
            'locations[name]' => 'Testing',
            'locations[description]' => 'Testing',
            'locations[light_condition]' => LightRequirement::bright->value,
            'locations[temperature_level]' => TemperatureRequirement::normal->value,
            'locations[humidity_level]' => HumidityRequirement::medium->value,
        ]);

        self::assertResponseRedirects('/locations');

        self::assertSame(1, $this->locationRepository->count([]));
    }

    public function testShow(): void
    {
        $fixture = $this->locations;

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Standort');
    }

    public function testEdit(): void
    {
        $fixture = $this->locations;

        $fixtureCreatedAt = $fixture->getCreatedAt();

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Bearbeiten', [
            'locations[name]' => 'New Title',
            'locations[description]' => 'New Description',
            'locations[light_condition]' => LightRequirement::halfshady->value,
            'locations[temperature_level]' => TemperatureRequirement::warm->value,
            'locations[humidity_level]' => HumidityRequirement::low->value,
        ]);

        self::assertResponseRedirects('/locations');

        $fixture = $this->locationRepository->findAll();

        self::assertSame('New Title', $fixture[0]->getName());
        self::assertSame('New Description', $fixture[0]->getDescription());
        self::assertSame(LightRequirement::halfshady, $fixture[0]->getLightCondition());
        self::assertSame(TemperatureRequirement::warm, $fixture[0]->getTemperatureLevel());
        self::assertSame(HumidityRequirement::low, $fixture[0]->getHumidityLevel());
        self::assertSame($fixtureCreatedAt->format('d.m.Y H:i:s'), $fixture[0]->getCreatedAt()->format('d.m.Y H:i:s'));
    }

    public function testRemove(): void
    {
        $fixture = $this->locations;

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects('/locations');
        self::assertSame(0, $this->locationRepository->count([]));
    }
}
