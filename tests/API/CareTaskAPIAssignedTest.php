<?php

namespace App\Tests\API;

use App\Entity\CareTask;
use App\Entity\CareHistory;
use App\Entity\Plants;
use App\Entity\TaskAssignments;
use App\Entity\User;
use App\Enum\CareType;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests the PR#41 extension to CareTaskAPIController:
 * a user who is assigned to a task (via TaskAssignments) can also mark it as done.
 */
class CareTaskAPIAssignedTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<TaskAssignments> */
    private EntityRepository $assignmentRepository;

    /** @var EntityRepository<CareHistory> */
    private EntityRepository $careHistoryRepository;

    private User $owner;
    private User $assignedUser;
    private User $unrelatedUser;
    private Plants $plant;
    private CareTask $careTask;

    private string $path = '/api/care_task/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->assignmentRepository = $this->manager->getRepository(TaskAssignments::class);
        $this->careHistoryRepository = $this->manager->getRepository(CareHistory::class);

        $userRepo = $this->manager->getRepository(User::class);

        foreach ($this->assignmentRepository->findAll() as $ta) {
            $this->manager->remove($ta);
        }
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        $this->manager->flush();

        $this->owner = $userRepo->findOneBy(['username' => 'Testuser']);
        $this->assignedUser = $userRepo->findOneBy(['username' => 'TestuserNoRef']);
        $this->unrelatedUser = $userRepo->findOneBy(['username' => 'TestuserNoPlants']);

        $this->plant = $this->createPlant($this->owner);
        $this->manager->persist($this->plant);
        $this->manager->flush();

        $this->careTask = $this->manager->getRepository(CareTask::class)
            ->findOneBy(['plant' => $this->plant]);
        self::assertNotNull($this->careTask);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->assignmentRepository->findAll() as $ta) {
            $this->manager->remove($ta);
        }
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        $this->manager->flush();
    }
    public function testAssignedUserCanMarkTaskDone(): void
    {
        $this->createAssignment($this->owner, $this->assignedUser)->getCareTask();

        $this->client->loginUser($this->assignedUser);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseIsSuccessful();

        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('care_history', $data);
        self::assertArrayHasKey('care_task', $data);

        $careHistory = $this->careHistoryRepository->find($data['care_history']['id']);
        self::assertSame($this->assignedUser->getId(), $careHistory->getUser()->getId());

        $assignment = $this->assignmentRepository->findOneBy(['care_task' => $this->careTask, 'to_user' => $this->assignedUser]);
        self::assertNull($assignment->getRespondedAt());
    }

    public function testAssignedTaskDueDateMovesAfterDone(): void
    {
        $oldDueDate = new \DateTimeImmutable('today 10:00');
        $this->careTask->setDueDate($oldDueDate);
        $this->manager->flush();

        $this->createAssignment($this->owner, $this->assignedUser);

        $this->client->loginUser($this->assignedUser);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseIsSuccessful();

        $this->manager->clear();
        $updatedTask = $this->manager->getRepository(CareTask::class)->find($this->careTask->getId());

        self::assertGreaterThan($oldDueDate, $updatedTask->getDueDate());
        self::assertSame(
            (new \DateTimeImmutable('+7 days'))->format('Y-m-d'),
            $updatedTask->getDueDate()->format('Y-m-d')
        );
    }

    public function testAssignedTaskDoneBeforeDueDateMovesFromPreviousDueDate(): void
    {
        $oldDueDate = new \DateTimeImmutable('tomorrow 10:00');
        $this->plant->setWateringIntervalDays(1);
        $this->careTask->setDueDate($oldDueDate);
        $this->manager->flush();

        $assignment = (new TaskAssignments())
            ->setFromUser($this->owner)
            ->setToUser($this->assignedUser)
            ->setCareTask($this->careTask)
            ->setStartDate(new \DateTimeImmutable('today'))
            ->setEndDate(new \DateTimeImmutable('+2 days 23:59'));

        $this->manager->persist($assignment);
        $this->manager->flush();

        $this->client->loginUser($this->assignedUser);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseIsSuccessful();

        $this->manager->clear();
        $updatedTask = $this->manager->getRepository(CareTask::class)->find($this->careTask->getId());

        self::assertSame(
            $oldDueDate->modify('+1 day')->format('Y-m-d'),
            $updatedTask->getDueDate()->format('Y-m-d')
        );
    }

    public function testOwnerCannotMarkActivelyAssignedTaskDone(): void
    {
        $this->createAssignment($this->owner, $this->assignedUser);

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseStatusCodeSame(403);
    }

    public function testUnrelatedUserCannotMarkTaskDone(): void
    {
        $thirdUser = $this->manager->getRepository(User::class)
            ->findOneBy(['username' => 'TestuserNoPlants']);

        $this->client->loginUser($thirdUser);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseStatusCodeSame(403);
    }
    public function testOwnerCanMarkTaskDone(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->careTask->getId());

        self::assertResponseIsSuccessful();
    }

    public function testUnauthenticatedIsRedirected(): void
    {
        $this->client->request('GET', $this->path . $this->careTask->getId());
        self::assertResponseRedirects('/login');
    }
    private function createAssignment(User $from, User $to): TaskAssignments
    {
        $assignment = (new TaskAssignments())
            ->setFromUser($from)
            ->setToUser($to)
            ->setCareTask($this->careTask)
            ->setStartDate(new \DateTimeImmutable('today'))
            ->setEndDate(new \DateTimeImmutable('+7 days'));

        $this->manager->persist($assignment);
        $this->manager->flush();

        return $assignment;
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('Testpflanze');
        $plant->setDescription('Beschreibung');
        $plant->setBotanicalName('Plantus testicus');
        $plant->setLightRequirement(LightRequirement::halfshady);
        $plant->setTemperatureRequirement(TemperatureRequirement::cool);
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
