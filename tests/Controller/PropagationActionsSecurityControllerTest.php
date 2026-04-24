<?php

namespace App\Tests\Controller;

use App\Entity\Plants;
use App\Entity\PropagationActions;
use App\Entity\User;
use App\Entity\WishlistPlants;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\PropagationMethod;
use App\Enum\Status;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class PropagationActionsSecurityControllerTest extends WebTestCase
{
    private EntityRepository $propagationActionsRepository;
    private EntityRepository $userRepository;
    private EntityRepository $plantRepository;
    private string $path = '/propagation_actions/';
    private PropagationActions $propagationAction;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->plantRepository = $this->manager->getRepository(Plants::class);
        $this->userRepository = $this->manager->getRepository(User::class);
        $this->propagationActionsRepository = $this->manager->getRepository(PropagationActions::class);

        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        foreach ($this->propagationActionsRepository->findAll() as $p) {
            $this->manager->remove($p);
        }

        $this->manager->flush();

        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);
        $plant = $this->createPlant($this->owner);
        $this->propagationAction = $this->createPropagationAction($plant);
        $this->manager->persist($plant);
        $this->manager->persist($this->propagationAction);
        $this->manager->flush();
    }

    public function testShowRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->propagationAction->getId());

        self::assertResponseRedirects('/propagation_actions');
    }

    public function testShowAllowedForOwner(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->propagationAction->getId());

        self::assertResponseIsSuccessful();
    }

    public function testEditGetRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->propagationAction->getId() . '/edit');

        self::assertResponseRedirects('/propagation_actions');
    }

    public function testEditPostRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->propagationAction->getId() . '/edit');

        self::assertResponseRedirects('/propagation_actions');


        $this->manager->refresh($this->propagationAction);
        self::assertSame(Status::planned->value, $this->propagationAction->getStatus()->value);
    }

    public function testDeleteRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->propagationAction->getId(), [
            '_token' => 'invalid',
        ]);

        self::assertResponseRedirects('/propagation_actions');

        self::assertSame(1, $this->propagationActionsRepository->count([]));
    }

    public function testDeleteWithInvalidCsrfTokenDoesNotDelete(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('POST', $this->path . $this->propagationAction->getId(), [
            '_token' => 'completely_wrong_token',
        ]);

        self::assertResponseRedirects('/propagation_actions');
        self::assertSame(1, $this->propagationActionsRepository->count([]));
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
