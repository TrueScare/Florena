<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class NotificationsControllerTest extends WebTestCase
{
    public function testIndexRedirectsToLoginForAnonymousUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/notifications');

        self::assertResponseRedirects('/login');
    }
}