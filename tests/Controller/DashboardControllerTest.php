<?php

namespace App\Tests\Controller;

use App\Entity\CareTask;
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

final class DashboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $user;
    private User $otherUser;

    /** @var EntityRepository<Plants> */
    private EntityRepository $plantRepository;

    /** @var EntityRepository<TaskAssignments> */
    private EntityRepository $taskAssignmentRepository;

    /** @var Plants[] */
    private array $createdPlants = [];

    /** @var TaskAssignments[] */
    private array $createdAssignments = [];

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->user = $this->em->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $this->em->getRepository(User::class)->findOneBy(['username' => 'TestuserNoRef']);
        $this->plantRepository = $this->em->getRepository(Plants::class);
        $this->taskAssignmentRepository = $this->em->getRepository(TaskAssignments::class);
    }

    protected function tearDown(): void
    {
        parent::tearDown();

        foreach ($this->createdAssignments as $assignment) {
            if ($this->em->contains($assignment)) {
                $this->em->remove($assignment);
            }
        }
        foreach ($this->createdPlants as $plant) {
            if ($this->em->contains($plant)) {
                $this->em->remove($plant);
            }
        }
        $this->em->flush();
    }

    public function testDashboardRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/dashboard');
        self::assertResponseRedirects('/login');
    }

    public function testDashboardRendersForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/dashboard');
        self::assertResponseIsSuccessful();
    }

    public function testDashboardShowsAssignedUpcomingTasks(): void
    {
        $plant = $this->createPlant($this->user, 'DashboardAssignedPlant');
        $this->em->persist($plant);
        $this->em->flush();

        $careTask = $this->em->getRepository(CareTask::class)->findOneBy(['plant' => $plant]);
        self::assertNotNull($careTask);

        $assignment = (new TaskAssignments())
            ->setFromUser($this->user)
            ->setToUser($this->otherUser)
            ->setCareTask($careTask)
            ->setStartDate($careTask->getDueDate()->modify('-1 hour'))
            ->setEndDate($careTask->getDueDate()->modify('+1 hour'));
        $this->createdAssignments[] = $assignment;

        $this->em->persist($assignment);
        $this->em->flush();

        $this->client->loginUser($this->otherUser);
        $crawler = $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('DashboardAssignedPlant', $crawler->text());
        self::assertStringContainsString('Übertragen von: Ich bin ein Testuser', $crawler->text());
    }

    public function testOwnerDashboardShowsTransferredTaskAsAssignedAway(): void
    {
        $plant = $this->createPlant($this->user, 'DashboardTransferredAwayPlant');
        $this->em->persist($plant);
        $this->em->flush();

        $careTask = $this->em->getRepository(CareTask::class)->findOneBy(['plant' => $plant]);
        self::assertNotNull($careTask);

        $assignment = (new TaskAssignments())
            ->setFromUser($this->user)
            ->setToUser($this->otherUser)
            ->setCareTask($careTask)
            ->setStartDate($careTask->getDueDate()->modify('-1 hour'))
            ->setEndDate($careTask->getDueDate()->modify('+14 days'));
        $this->createdAssignments[] = $assignment;

        $this->em->persist($assignment);
        $this->em->flush();

        $this->client->loginUser($this->user);
        $crawler = $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('DashboardTransferredAwayPlant', $crawler->text());
        self::assertStringContainsString('Übergeben an: Ich bin ein Testuser ohne Referenzdaten', $crawler->text());
    }

    public function testAssignedDashboardShowsNextDueDateWhenStillInsideAssignmentWindow(): void
    {
        $plant = $this->createPlant($this->user, 'DashboardRecurringAssignedPlant');
        $plant->setWateringIntervalDays(1);
        $this->em->persist($plant);
        $this->em->flush();

        $careTask = $this->em->getRepository(CareTask::class)->findOneBy(['plant' => $plant]);
        self::assertNotNull($careTask);
        $careTask->setDueDate(new \DateTimeImmutable('tomorrow 10:00'));

        $assignment = (new TaskAssignments())
            ->setFromUser($this->user)
            ->setToUser($this->otherUser)
            ->setCareTask($careTask)
            ->setStartDate(new \DateTimeImmutable('today'))
            ->setEndDate(new \DateTimeImmutable('+3 days'));
        $this->createdAssignments[] = $assignment;

        $this->em->persist($assignment);
        $this->em->flush();

        $this->client->loginUser($this->otherUser);
        $crawler = $this->client->request('GET', '/dashboard');

        self::assertResponseIsSuccessful();
        self::assertStringContainsString('DashboardRecurringAssignedPlant', $crawler->text());
        self::assertStringContainsString('Übertragen von: Ich bin ein Testuser', $crawler->text());
    }

    private function createPlant(User $user, string $name): Plants
    {
        $plant = new Plants();
        $plant->setName($name);
        $plant->setDescription('desc');
        $plant->setBotanicalName('Dashboardus testicus');
        $plant->setLightRequirement(LightRequirement::halfshady);
        $plant->setTemperatureRequirement(TemperatureRequirement::cool);
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
        $this->createdPlants[] = $plant;

        return $plant;
    }
}
