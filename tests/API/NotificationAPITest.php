<?php

namespace App\Tests\API;

use App\Entity\Notifications;
use App\Entity\Plants;
use App\Entity\User;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NotificationAPITest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $owner;
    private User $otherUser;
    private Plants $plant;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();

        $userRepo = $this->em->getRepository(User::class);
        $this->owner = $userRepo->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $userRepo->findOneBy(['username' => 'TestuserNoRef']);

        foreach ($this->em->getRepository(Notifications::class)->findAll() as $n) {
            $this->em->remove($n);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();

        $this->plant = $this->createPlant($this->owner);
        $this->em->persist($this->plant);
        $this->em->flush();
    }


    public function testReadRedirectsWhenNotLoggedIn(): void
    {
        $notification = $this->createNotification($this->owner);
        $this->client->request('POST', '/api/notification/' . $notification->getId() . '/read');
        self::assertResponseRedirects('/login');
    }

    public function testOwnerCanMarkNotificationAsRead(): void
    {
        $notification = $this->createNotification($this->owner);
        self::assertFalse($notification->isRead());

        $this->client->loginUser($this->owner);
        $this->client->request('POST', '/api/notification/' . $notification->getId() . '/read');

        self::assertResponseIsSuccessful();
        $data = json_decode($this->client->getResponse()->getContent(), true);
        self::assertTrue($data['success']);
        self::assertSame($notification->getId(), $data['notificationId']);

        $this->em->clear();
        $updated = $this->em->getRepository(Notifications::class)->find($notification->getId());
        self::assertTrue($updated->isRead());
    }
    public function testOtherUserCannotMarkNotificationAsRead(): void
    {
        $notification = $this->createNotification($this->owner);

        $this->client->loginUser($this->otherUser);
        $this->client->request('POST', '/api/notification/' . $notification->getId() . '/read');

        self::assertResponseStatusCodeSame(403);

        // notification must still be unread
        $this->em->clear();
        $unchanged = $this->em->getRepository(Notifications::class)->find($notification->getId());
        self::assertFalse($unchanged->isRead());
    }

    private function createNotification(User $user): Notifications
    {
        $careTask = $this->em->getRepository(\App\Entity\CareTask::class)
            ->findOneBy(['plant' => $this->plant]);

        $n = new Notifications();
        $n->setUser($user);
        $n->setMessage('Test notification');
        $n->setCareTask($careTask);

        $this->em->persist($n);
        $this->em->flush();

        return $n;
    }

    private function createPlant(User $user): Plants
    {
        $plant = new Plants();
        $plant->setName('NotifTestPlant');
        $plant->setDescription('desc');
        $plant->setBotanicalName('Notificus testicus');
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
