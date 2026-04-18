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

final class PlantsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;
    private string $path = '/plants/';
    private EntityRepository $userRepository;
    private ?Plants $plant = null;
    private ?User $user = null;

    private EntityRepository $careTaskRepository;

    protected function setUp(): void
    {
        $this->client = PlantsControllerTest::createClient();
        $this->manager = PlantsControllerTest::getContainer()->get('doctrine')->getManager();
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->careTaskRepository = $this->manager->getRepository(CareTask::class);

        foreach ($this->plantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->userRepository = $this->manager->getRepository(User::class);
        $this->user = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->client->loginUser($this->user);

        $this->plant = new Plants();
        $this->plant->setName("Testplant");
        $this->plant->setDescription("Description");
        $this->plant->setBotanicalName("BotanicalName");
        $this->plant->setLightRequirement(LightRequirement::halfshady);
        $this->plant->setTemperatureRequirement(TemperatureRequirement::cool);
        $this->plant->setHumidityRequirement(HumidityRequirement::medium);
        $this->plant->setSoilType("TestSoilType");
        $this->plant->setPotSize("20 cm");
        $this->plant->setLastFertilizedAt(new \DateTimeImmutable());
        $this->plant->setLastRepottedAt(new \DateTimeImmutable());
        $this->plant->setLastWateredAt(new \DateTimeImmutable());
        $this->plant->setWateringIntervalDays(15);
        $this->plant->setFertilizingIntervalDays(51);
        $this->plant->setRepottingIntervalDays(465);
        $this->plant->setToxicForAnimals(false);
        $this->plant->setToxicForHumans(false);
        $this->plant->setPurchaseDate(new \DateTimeImmutable());
        $this->plant->setStressScore(100);
        $this->plant->setUser($this->user);

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Pflanzen');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Pflanze speichern', [
            'plants[name]' => 'Testname',
            'plants[description]' => 'New Description',
            'plants[botanical_name]' => 'Botanical NAAAME',
            'plants[image]' => '',
            'plants[light_requirement]' => LightRequirement::bright->value,
            'plants[temperature_requirement]' => TemperatureRequirement::cool->value,
            'plants[humidity_requirement]' => HumidityRequirement::medium->value,
            'plants[soil_type]' => 'Whatever Soil',
            'plants[pot_size]' => '35 cm',
            'plants[watering_interval_days]' => 4,
            'plants[fertilizing_interval_days]' => 35,
            'plants[repotting_interval_days]' => 564,
            /* These will be the same as created_at
            'plants[last_watered_at]' => "",
            'plants[last_fertilized_at]' => "",
            'plants[last_repotted_at]' => "",
            */
            'plants[toxic_for_humans]' => true,
            'plants[toxic_for_animals]' => false,
            'plants[purchase_date]' => "2026-04-12T21:17",
        ]);

        self::assertResponseRedirects('/plants');
        $fixture = $this->plantRepository->findBy(['name' => 'Testname']);
        self::assertSame(1, count($fixture));

        $tasks = $this->careTaskRepository->findBy(['plant' => $fixture[0]]);
        $taskTypes = array_map(fn(CareTask $t) => $t->getTaskType(), $tasks);
        self::assertContains(CareType::water, $taskTypes);
        self::assertContains(CareType::repot, $taskTypes);
        self::assertContains(CareType::fertilice, $taskTypes);
    }

    public function testShow(): void
    {
        $fixture = $this->plant;

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Plants - Testplant');
    }

    public function testEdit(): void
    {
        $fixture = $this->plant;
        $fixtureCreatedAt = $fixture->getCreatedAt();

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $newCreatedAt = "2026-04-12 21:17";

        $this->client->submitForm('Bearbeiten', [
            'plants[name]' => 'Testname neu',
            'plants[description]' => 'New Description neu',
            'plants[botanical_name]' => 'Botanical NAAAME neu',
            'plants[image]' => '',
            'plants[light_requirement]' => LightRequirement::halfshady->value,
            'plants[temperature_requirement]' => TemperatureRequirement::normal->value,
            'plants[humidity_requirement]' => HumidityRequirement::low->value,
            'plants[soil_type]' => 'Whatever Soil new',
            'plants[pot_size]' => '35 cm new',
            'plants[watering_interval_days]' => 2,
            'plants[fertilizing_interval_days]' => 0,
            'plants[repotting_interval_days]' => 580,
            'plants[last_watered_at]' => $newCreatedAt,
            'plants[last_fertilized_at]' => $newCreatedAt,
            'plants[last_repotted_at]' => $newCreatedAt,
            'plants[toxic_for_humans]' => false,
            'plants[toxic_for_animals]' => true,
            'plants[purchase_date]' => $newCreatedAt,
        ]);

        self::assertResponseRedirects('/plants');

        $fixture = $this->plantRepository->findOneBy(['id' => $fixture->getId()]);

        self::assertSame('Testname neu', $fixture->getName());
        self::assertSame('New Description neu', $fixture->getDescription());
        self::assertSame('Botanical NAAAME neu', $fixture->getBotanicalName());
        self::assertSame(null, $fixture->getPhotoPath());
        self::assertSame(LightRequirement::halfshady->value, $fixture->getLightRequirement()->value);
        self::assertSame(TemperatureRequirement::normal->value, $fixture->getTemperatureRequirement()->value);
        self::assertSame(HumidityRequirement::low->value, $fixture->getHumidityRequirement()->value);
        self::assertSame('Whatever Soil new', $fixture->getSoilType());
        self::assertSame('35 cm new', $fixture->getPotSize());
        self::assertSame(2, $fixture->getWateringIntervalDays());
        self::assertSame(0, $fixture->getFertilizingIntervalDays());
        self::assertSame(580, $fixture->getRepottingIntervalDays());
        self::assertSame($newCreatedAt, $fixture->getLastWateredAt()->format("Y-m-d H:i"));
        self::assertSame($newCreatedAt, $fixture->getLastFertilizedAt()->format("Y-m-d H:i"));
        self::assertSame($newCreatedAt, $fixture->getLastRepottedAt()->format("Y-m-d H:i"));
        self::assertSame(false, $fixture->isToxicForHumans());
        self::assertSame(true, $fixture->isToxicForAnimals());
        self::assertSame($newCreatedAt, $fixture->getPurchaseDate()->format("Y-m-d H:i"));
        self::assertSame($fixtureCreatedAt->format("Y-m-d H:i:s"), $fixture->getCreatedAt()->format("Y-m-d H:i:s"));

        $tasks = $this->careTaskRepository->findBy(['plant' => $fixture]);
        self::assertCount(2, $tasks);

        $taskTypes = array_map(fn(CareTask $t) => $t->getTaskType(), $tasks);
        self::assertContains(CareType::water, $taskTypes);
        self::assertContains(CareType::repot, $taskTypes);
        self::assertNotContains(CareType::fertilice, $taskTypes);

        $waterTask = array_values(array_filter($tasks, fn(CareTask $t) => $t->getTaskType() === CareType::water))[0];
        $expectedDueDate = (new \DateTimeImmutable($newCreatedAt))->modify('+2 days');
        self::assertSame($expectedDueDate->format('Y-m-d'), $waterTask->getDueDate()->format('Y-m-d'));
    }

    public function testRemove(): void
    {
        $fixture = $this->plant;

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects('/plants');
        self::assertSame(0, $this->plantRepository->count([]));
        self::assertSame(0, $this->careTaskRepository->count([]));
    }
}
