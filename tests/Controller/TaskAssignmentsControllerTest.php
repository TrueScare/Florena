<?php

namespace App\Tests\Controller;

use App\Entity\TaskAssignments;
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
    private string $path = '/task/assignments/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->taskAssignmentRepository = $this->manager->getRepository(TaskAssignments::class);

        foreach ($this->taskAssignmentRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('TaskAssignment index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first()->text());
    }

    public function testNew(): void
    {
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'task_assignment[start_date]' => 'Testing',
            'task_assignment[end_date]' => 'Testing',
            'task_assignment[assigned_at]' => 'Testing',
            'task_assignment[responded_at]' => 'Testing',
            'task_assignment[from_user]' => 'Testing',
            'task_assignment[to_user]' => 'Testing',
            'task_assignment[care_task]' => 'Testing',
        ]);

        self::assertResponseRedirects('/task/assignments');

        self::assertSame(1, $this->taskAssignmentRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }

    public function testShow(): void
    {
        $fixture = new TaskAssignments();
        $fixture->setStartDate('My Title');
        $fixture->setEndDate('My Title');
        $fixture->setAssignedAt('My Title');
        $fixture->setRespondedAt('My Title');
        $fixture->setFromUser('My Title');
        $fixture->setToUser('My Title');
        $fixture->setCareTask('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('TaskAssignment');

        // Use assertions to check that the properties are properly displayed.
        $this->markTestIncomplete('This test was generated');
    }

    public function testEdit(): void
    {
        $fixture = new TaskAssignments();
        $fixture->setStartDate('Value');
        $fixture->setEndDate('Value');
        $fixture->setAssignedAt('Value');
        $fixture->setRespondedAt('Value');
        $fixture->setFromUser('Value');
        $fixture->setToUser('Value');
        $fixture->setCareTask('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'task_assignment[start_date]' => 'Something New',
            'task_assignment[end_date]' => 'Something New',
            'task_assignment[assigned_at]' => 'Something New',
            'task_assignment[responded_at]' => 'Something New',
            'task_assignment[from_user]' => 'Something New',
            'task_assignment[to_user]' => 'Something New',
            'task_assignment[care_task]' => 'Something New',
        ]);

        self::assertResponseRedirects('/task/assignments');

        $fixture = $this->taskAssignmentRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getStartDate());
        self::assertSame('Something New', $fixture[0]->getEndDate());
        self::assertSame('Something New', $fixture[0]->getAssignedAt());
        self::assertSame('Something New', $fixture[0]->getRespondedAt());
        self::assertSame('Something New', $fixture[0]->getFromUser());
        self::assertSame('Something New', $fixture[0]->getToUser());
        self::assertSame('Something New', $fixture[0]->getCareTask());

        $this->markTestIncomplete('This test was generated');
    }

    public function testRemove(): void
    {
        $fixture = new TaskAssignments();
        $fixture->setStartDate('Value');
        $fixture->setEndDate('Value');
        $fixture->setAssignedAt('Value');
        $fixture->setRespondedAt('Value');
        $fixture->setFromUser('Value');
        $fixture->setToUser('Value');
        $fixture->setCareTask('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/task/assignments');
        self::assertSame(0, $this->taskAssignmentRepository->count([]));

        $this->markTestIncomplete('This test was generated');
    }
}
