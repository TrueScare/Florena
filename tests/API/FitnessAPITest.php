<?php

namespace App\Tests\API;

use App\Entity\Locations;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class FitnessAPITest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->user = $this->em->getRepository(User::class)->findOneBy(['username' => 'Testuser']);

        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        foreach ($this->em->getRepository(Locations::class)->findAll() as $l) {
            $this->em->remove($l);
        }
        $this->em->flush();

        // Create a location and a plant with identical requirements so fitness = perfect
        $loc = new Locations();
        $loc->setName('APIFitnessLoc');
        $loc->setDescription('desc');
        $loc->setLightCondition(LightRequirement::bright);
        $loc->setTemperatureLevel(TemperatureRequirement::normal);
        $loc->setHumidityLevel(HumidityRequirement::medium);
        $loc->setUser($this->user);
        $this->em->persist($loc);

        $plant = new Plants();
        $plant->setName('APIFitnessPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Plantus apicus');
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
        $plant->setUser($this->user);
        $this->em->persist($plant);

        $this->em->flush();
    }

    public function testLocationFitnessRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/api/fitness/location');
        self::assertResponseRedirects('/login');
    }

    public function testLocationFitnessReturns422WhenParamsMissing(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/fitness/location');
        self::assertResponseStatusCodeSame(422);
    }

    public function testLocationFitnessReturns422ForInvalidEnumValue(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/fitness/location', [
            'light_requirement'       => 'nonexistent',
            'temperature_requirement' => 'normal',
            'humidity_requirement'    => 'medium',
        ]);
        self::assertResponseStatusCodeSame(422);
    }

    public function testLocationFitnessReturnsJsonWithValidParams(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/fitness/location', [
            'light_requirement'       => LightRequirement::bright->value,
            'temperature_requirement' => TemperatureRequirement::normal->value,
            'humidity_requirement'    => HumidityRequirement::medium->value,
        ]);


        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        // All requirements match → result should be under "perfect"
        self::assertArrayHasKey('geeignet', $data);
    }
    public function testPlantFitnessRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/api/fitness/plant');
        self::assertResponseRedirects('/login');
    }

    public function testPlantFitnessReturns422WhenParamsMissing(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/fitness/plant');
        self::assertResponseStatusCodeSame(422);
    }

    public function testPlantFitnessReturnsJsonWithValidParams(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/api/fitness/plant', [
            'light_condition'     => LightRequirement::bright->value,
            'temperature_level'   => TemperatureRequirement::normal->value,
            'humidity_level'      => HumidityRequirement::medium->value,
        ]);

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('geeignet', $data);
    }
}
