<?php

namespace App\Tests\Controller;

use App\Entity\Plants;
use App\Entity\PropagationActions;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\PropagationMethod;
use App\Enum\Status;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class PropagationActionsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<PropagationActions> */
    private EntityRepository $propagationActionRepository;
    private string $path = '/propagation_actions/';
    private EntityRepository $plantRepository;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->propagationActionRepository = $this->manager->getRepository(PropagationActions::class);
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->user = $this->manager->getRepository(User::class)->findOneBy(['username' => 'Testuser']);

        foreach ($this->propagationActionRepository->findAll() as $object) {
            $this->manager->remove($object);
        }
        foreach ($this->plantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testNew(): void
    {
        $this->client->loginUser($this->user);

        $plant = $this->createPlant($this->user);
        $this->manager->persist($plant);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Speichern', [
            'propagation_actions[method]' => PropagationMethod::rootdevision->value,
            'propagation_actions[planned_date]' => new \DateTimeImmutable()->format('Y-m-d H:i'),
            'propagation_actions[status]' => Status::planned->value,
            'propagation_actions[notes]' => "Testnotes",
            'propagation_actions[plant]' => $plant->getId(),
        ]);

        self::assertResponseRedirects('/plants/' . $plant->getId());

        self::assertSame(1, $this->propagationActionRepository->count([]));
    }

    public function testEdit(): void
    {
        $this->client->loginUser($this->user);

        $plant =$this->createPLant($this->user);
        $fixture = $this->createPropagationAction($plant);

        $this->manager->persist($plant);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $date = new \DateTimeImmutable()->format('Y-m-d H:i');

        $this->client->submitForm('Bearbeiten', [
            'propagation_actions[method]' => PropagationMethod::cuttings->value,
            'propagation_actions[planned_date]' => $date,
            'propagation_actions[status]' => Status::in_progress->value,
            'propagation_actions[notes]' => 'neue notiz'
        ]);

        self::assertResponseRedirects('/plants/' . $plant->getId());

        $fixture = $this->propagationActionRepository->findAll();

        self::assertSame(PropagationMethod::cuttings, $fixture[0]->getMethod());
        self::assertSame($date, $fixture[0]->getPlannedDate()->format('Y-m-d H:i'));
        self::assertSame(Status::in_progress, $fixture[0]->getStatus());
        self::assertSame('neue notiz', $fixture[0]->getNotes());
        self::assertSame($plant->getId(), $fixture[0]->getPlant()->getId());
    }

    public function testRemove(): void
    {
        $this->client->loginUser($this->user);

        $plant = $this->createPlant($this->user);
        $fixture = $this->createPropagationAction($plant);

        $this->manager->persist($plant);
        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects('/plants/' . $plant->getId());
        self::assertSame(0, $this->propagationActionRepository->count([]));
    }

    private function createPlant(User $user): Plants
    {
        return new Plants()
            ->setName("Testplant")
            ->setDescription("Description")
            ->setBotanicalName("BotanicalName")
            ->setLightRequirement(LightRequirement::halfshady)
            ->setTemperatureRequirement(TemperatureRequirement::cool)
            ->setHumidityRequirement(HumidityRequirement::medium)
            ->setSoilType("TestSoilType")
            ->setPotSize("20 cm")
            ->setLastFertilizedAt(new \DateTimeImmutable())
            ->setLastRepottedAt(new \DateTimeImmutable())
            ->setLastWateredAt(new \DateTimeImmutable())
            ->setWateringIntervalDays(15)
            ->setFertilizingIntervalDays(51)
            ->setRepottingIntervalDays(465)
            ->setToxicForAnimals(false)
            ->setToxicForHumans(false)
            ->setPurchaseDate(new \DateTimeImmutable())
            ->setStressScore(100)
            ->setUser($user);
    }

    private function createPropagationAction(Plants $plant): PropagationActions
    {
        return new PropagationActions()
            ->setMethod(PropagationMethod::cuttings)
            ->setPlannedDate(new \DateTimeImmutable()->modify("+15 days"))
            ->setStatus(Status::planned)
            ->setNotes("meine testnotizen")
            ->setPlant($plant);
    }
}
