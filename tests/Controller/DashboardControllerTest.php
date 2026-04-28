<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class DashboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $this->user = $em->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
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
}
