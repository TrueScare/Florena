<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Bundle\FixturesBundle\FixtureGroupInterface;
use Doctrine\Persistence\ObjectManager;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserFixture extends Fixture implements FixtureGroupInterface
{
    public const string TEST_USER = 'testuser';
    public const string TEST_USER_NO_REFERENCE = 'testuser_no_reference';
    private ?UserPasswordHasherInterface $passwordHasher;

    public function __construct(UserPasswordHasherInterface $passwordHasher)
    {
        $this->passwordHasher = $passwordHasher;
    }

    public function load(ObjectManager $manager): void
    {
        $user1 = new User();
        $user1->setUsername('Testuser');
        $user1->setDisplayname('Ich bin ein Testuser');
        $user1->setEmail('Test@test.de');
        $user1->setPassword($this->passwordHasher->hashPassword($user1, '!1234567'));

        $user2 = new User();
        $user2->setUsername('TestuserNoRef');
        $user2->setDisplayname('Ich bin ein Testuser ohne Referenzdaten');
        $user2->setEmail('Test@test.de');
        $user2->setPassword($this->passwordHasher->hashPassword($user1, '!1234567'));

        $manager->persist($user1);
        $manager->flush();

        $this->addReference(self::TEST_USER, $user1);
        $this->addReference(self::TEST_USER_NO_REFERENCE, $user2);
    }

    public static function getGroups(): array
    {
        return  [
            'users',
            'test_data'
        ];
    }
}
