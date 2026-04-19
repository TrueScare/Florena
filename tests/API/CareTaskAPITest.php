<?php

namespace App\Tests\API;

use App\Entity\CareHistory;
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
use Symfony\Component\Serializer\SerializerInterface;

class CareTaskAPITest extends WebTestCase
{
    private KernelBrowser $client;
    private string $path = '/api/care_task/';
    private EntityRepository $userRepository;
    private EntityRepository $plantRepository;
    private EntityRepository $careTaskRepository;
    private User $owner;
    private User $attacker;
    private Plants $plant;
    private EntityManagerInterface $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->careTaskRepository = $this->manager->getRepository(CareTask::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        // make sure we have a fresh plant for every run
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }

        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);
        $this->plant = $this->createPlant($this->owner);

        $this->manager->persist($this->plant);
        $this->manager->flush();
    }

    public function testSetTaskDoneWhenNotLoggedIn(): void
    {
        $task = $this->careTaskRepository->findOneBy(['plant' => $this->plant]);
        $this->client->request('GET', sprintf('%s%s',$this->path, $task->getId()));
        self::assertEquals(302, $this->client->getResponse()->getStatusCode());
        self::assertResponseRedirects('/login');
    }

    public function testSetTaskDoneLoggedInWrongUser(): void
    {
        $this->client->loginUser($this->attacker);
        $task = $this->careTaskRepository->findOneBy(['plant' => $this->plant]);
        $this->client->request('GET', sprintf('%s%s',$this->path, $task->getId()));
        self::assertEquals(403, $this->client->getResponse()->getStatusCode());
    }

    public function testSetTaskDoneWhenLoggedIn(): void
    {
        $this->client->loginUser($this->owner);
        $task = $this->careTaskRepository->findOneBy(['plant' => $this->plant, 'task_type' => CareType::water->value]);
        $this->client->request('GET', sprintf('%s%s',$this->path, $task->getId()));
        $data = json_decode($this->client->getResponse()->getContent(), true);

        $careTask = $data['care_task'];
        $careHistory = $data['care_history'];
        self::assertNotNull($careTask);
        self::assertNotNull($careHistory);

        // requery plant to get changed data
        $this->plant = $this->plantRepository->findOneBy(['id' => $this->plant->getId()]);
        $taskPerformedAt = new \DateTimeImmutable($careHistory['performed_at']);
        self::assertEquals($taskPerformedAt->format('d.m.Y H:i:s'), $this->plant->getLastWateredAt()->format('d.m.Y H:i:s'));
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
