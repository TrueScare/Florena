<?php

namespace App\Tests\Controller;

use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\TaskAssignments;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class TaskAssignmentsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<TaskAssignments> */
    private EntityRepository $taskAssignmentRepository;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    private User $owner;
    private User $otherUser;
    private Plants $plant;
    private CareTask $careTask;

    private string $path = '/task_assignments';
    private EntityRepository $notificationRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->taskAssignmentRepository = $this->manager->getRepository(TaskAssignments::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);
        $this->notificationRepository = $this->manager->getRepository(Notifications::class);

        foreach ($this->taskAssignmentRepository->findAll() as $ta) {
            $this->manager->remove($ta);
        }
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach ($this->notificationRepository->findAll() as $n){
            $this->manager->remove($n);
        }
        $this->manager->flush();

        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);

        $this->plant = $this->createPlant($this->owner);
        $this->manager->persist($this->plant);
        $this->manager->flush();

        $this->careTask = $this->manager->getRepository(CareTask::class)
            ->findOneBy(['plant' => $this->plant]);
        self::assertNotNull($this->careTask, 'PlantsUpdateListener must have created a CareTask');
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->taskAssignmentRepository->findAll() as $ta) {
            $this->manager->remove($ta);
        }
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach ($this->notificationRepository->findAll() as $n){
            $this->manager->remove($n);
        }
        $this->manager->flush();
    }

    public function testIndexRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', $this->path);
        self::assertResponseRedirects('/login');
    }

    public function testNewRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', $this->path . '/new');
        self::assertResponseRedirects('/login');
    }
    public function testIndexRendersForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path);

        self::assertResponseIsSuccessful();
    }
    public function testNewFormRendersForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/new');

        self::assertResponseIsSuccessful();
    }

    public function testNewCreatesAssignmentsForSelectedTasks(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/new');

        $this->client->submitForm('Speichern', [
            'task_assignments[start_date]' => (new \DateTimeImmutable('+1 day'))->format('Y-m-d H:i'),
            'task_assignments[end_date]'   => (new \DateTimeImmutable('+7 days'))->format('Y-m-d H:i'),
            'task_assignments[to_user]'    => $this->otherUser->getId(),
            'task_assignments[care_tasks]' => [$this->careTask->getId()],
        ]);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->taskAssignmentRepository->count([]));

        $assignment = $this->taskAssignmentRepository->findAll()[0];
        self::assertSame($this->otherUser->getId(), $assignment->getToUser()->getId());
        self::assertSame($this->owner->getId(), $assignment->getFromUser()->getId());
        self::assertSame($this->careTask->getId(), $assignment->getCareTask()->getId());
    }
    public function testShowAllowedForToUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', $this->path . '/' . $assignment->getId());

        self::assertResponseIsSuccessful();
    }

    public function testShowAllowedForFromUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/' . $assignment->getId());

        self::assertResponseIsSuccessful();
    }

    public function testShowRedirectsForUnrelatedUser(): void
    {
        $thirdUser = $this->userRepository->findOneBy(['username' => 'TestuserNoPlants']);

        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($thirdUser);
        $this->client->request('GET', $this->path . '/' . $assignment->getId());

        self::assertResponseRedirects($this->path);
    }

    public function testEditAllowedForFromUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/' . $assignment->getId() . '/edit');

        self::assertResponseIsSuccessful();
    }

    public function testEditRedirectsForToUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', $this->path . '/' . $assignment->getId() . '/edit');

        self::assertResponseRedirects($this->path);
    }

    public function testEditPostRedirectsForToUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->otherUser);
        $this->client->request('POST', $this->path . '/' . $assignment->getId() . '/edit');

        self::assertResponseRedirects($this->path);
    }

    public function testEditUpdatesEndDate(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);
        $newEnd = (new \DateTimeImmutable('+14 days'))->format('Y-m-d H:i');

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/' . $assignment->getId() . '/edit');

        $this->client->submitForm('Bearbeiten', [
            'task_assignments[start_date]' => $assignment->getStartDate()->format('Y-m-d H:i'),
            'task_assignments[end_date]'   => $newEnd,
            'task_assignments[to_user]'    => $this->otherUser->getId(),
            'task_assignments[care_task]'  => $this->careTask->getId(),
        ]);

        self::assertResponseRedirects($this->path);

        $this->manager->clear();
        $updated = $this->taskAssignmentRepository->find($assignment->getId());
        self::assertSame($newEnd, $updated->getEndDate()->format('Y-m-d H:i'));
    }
    public function testDeleteAllowedForFromUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . '/' . $assignment->getId());
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->taskAssignmentRepository->count([]));
    }

    public function testDeleteAllowedForToUser(): void
    {
        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', $this->path . '/' . $assignment->getId());
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects($this->path);
        self::assertSame(0, $this->taskAssignmentRepository->count([]));
    }

    public function testDeleteBlockedForUnrelatedUser(): void
    {
        $thirdUser = $this->userRepository->findOneBy(['username' => 'TestuserNoPlants']);

        $assignment = $this->createAssignment($this->owner, $this->otherUser);

        // Log in as the unrelated user and send a POST directly.
        // The controller checks ownership BEFORE CSRF, so the user is redirected away
        // regardless of token validity. Using an invalid token keeps us from needing
        // a session-bound CSRF manager outside of a real request context.
        $this->client->loginUser($thirdUser);
        $this->client->request('POST', $this->path . '/' . $assignment->getId(), ['_token' => 'invalid']);

        self::assertResponseRedirects($this->path);
        self::assertSame(1, $this->taskAssignmentRepository->count([]));

    }
    private function createAssignment(User $from, User $to): TaskAssignments
    {
        $assignment = (new TaskAssignments())
            ->setFromUser($from)
            ->setToUser($to)
            ->setCareTask($this->careTask)
            ->setStartDate(new \DateTimeImmutable('+1 day'))
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
