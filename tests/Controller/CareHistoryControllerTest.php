<?php

namespace App\Tests\Controller;

use App\Entity\CareHistory;
use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CareHistoryControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<CareHistory> */
    private string $path = '/care_history/';
    private ?User $user;

    private CareTask $task;
    private Plants $plant;
    private EntityRepository $plantRepository;
    private EntityRepository $notificationRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $userRepository = $this->manager->getRepository(User::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->notificationRepository = $this->manager->getRepository(Notifications::class);
        $careTaskRepository = $this->manager->getRepository(CareTask::class);

        $this->user = $userRepository->findOneBy(['username' => 'Testuser']);
        $this->plant = $this->createPlant($this->user);

        $this->manager->persist($this->plant);
        $this->manager->flush();

        // reload data after persisting into db and thus creating tasks
        /** @var Plants $plant */
        $this->plant = $this->plantRepository->findOneBy(['name' => $this->plant->getName()]);
        // fetch anyone of the tasks
        $this->task = $careTaskRepository->findOneBy(['plant' => $this->plant]);
    }

    protected function tearDown(): void
    {
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach($this->notificationRepository->findAll() as $n){
            $this->manager->remove($n);
        }
        $this->manager->flush();

        parent::tearDown();
    }


    public function testIndex(): void
    {
        $this->client->loginUser($this->user);

        // set task as done and creating a history
        $this->client->request('GET', '/api/care_task/' . $this->task->getId());

        /** @var CareHistory $history */
        $history = $this->manager->getRepository(CareHistory::class)->findAll()[0];

        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Pflegehistorie');

        // the name should be in there
        self::assertStringContainsString($this->plant->getName(), $crawler->html());
        self::assertStringContainsString($history->getCareType()->value, $crawler->html());
        self::assertStringContainsString($history->getPerformedAt()->format('Y-m-d'), $crawler->html());
    }

    public function testIndexWhenNotAutherized(): void
    {
        $this->client->followRedirects();
        $this->client->request('GET', $this->path);
        self::assertStringContainsString('/login', $this->client->getRequest()->getUri());
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('CareHistoryTestPlant');
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
