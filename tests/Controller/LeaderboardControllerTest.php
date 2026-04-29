<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class LeaderboardControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $em = static::getContainer()->get('doctrine')->getManager();
        $this->user = $em->getRepository(User::class)->findOneBy(['username' => 'Testuser']);
    }

    public function testLeaderboardRedirectsWhenNotLoggedIn(): void
    {
        $this->client->request('GET', '/leaderboard');
        self::assertResponseRedirects('/login');
    }

    public function testLeaderboardRendersForAuthenticatedUser(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', '/leaderboard');
        self::assertResponseIsSuccessful();
    }
}
