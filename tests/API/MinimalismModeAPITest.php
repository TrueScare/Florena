<?php

namespace App\Tests\API;

use App\Entity\User;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class MinimalismModeAPITest extends WebTestCase
{
    private User $user;
    private KernelBrowser $client;
    private ObjectManager $manager;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();

        $this->user = $this->manager->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
    }

    public function testMinimalismToggle()
    {
        self::assertFalse($this->user->isMinimalMode());
        $this->client->loginUser($this->user);

        // toggle from false to true
        $this->client->request('GET', $this->getContainer()->get('router')->generate('app_api_minimalism_toggle'));
        $this->user = $this->manager->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
        self::assertTrue($this->client->getResponse()->isSuccessful());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        self::assertNotNull($json['user']);
        self::assertTrue($json['user']['is_minimal_mode']);
        self::assertTrue($this->user->isMinimalMode());

        // toggle from true to false
        $this->client->request('GET', $this->getContainer()->get('router')->generate('app_api_minimalism_toggle'));
        $this->user = $this->manager->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
        self::assertTrue($this->client->getResponse()->isSuccessful());
        $json = json_decode($this->client->getResponse()->getContent(), true);
        self::assertNotNull($json['user']);
        self::assertFalse($json['user']['is_minimal_mode']);
        self::assertFalse($this->user->isMinimalMode());
    }


}
