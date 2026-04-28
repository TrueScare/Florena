<?php

namespace App\Tests\Controller;

use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NotificationsControllerFullTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $this->user = $this->em->getRepository(User::class)->findOneBy(['username' => 'Testuser']);

        foreach ($this->em->getRepository(Notifications::class)->findAll() as $n) {
            $this->em->remove($n);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();

        $plant = $this->createPlant($this->user);
        $this->em->persist($plant);
        $this->em->flush();
    }


    public function testIndexRendersForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/notifications');
        self::assertResponseIsSuccessful();
    }

    public function testCountEndpointReturnsJson(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/notifications/count');

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertArrayHasKey('count', $data);
        self::assertIsInt($data['count']);
    }

    public function testCountEndpointRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/notifications/count');
        self::assertResponseRedirects('/login');
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('NotifCtrlPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Notificus');
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
        return $plant;
    }
}
