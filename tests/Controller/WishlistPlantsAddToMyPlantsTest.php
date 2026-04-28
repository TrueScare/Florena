<?php

namespace App\Tests\Controller;

use App\Entity\Plants;
use App\Entity\User;
use App\Entity\WishlistPlants;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

/**
 * Tests the "add to my plants" action on WishlistPlantsController,
 * which was not covered by existing tests.
 */
final class WishlistPlantsAddToMyPlantsTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $em;
    private User $owner;
    private User $otherUser;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->em = static::getContainer()->get('doctrine')->getManager();
        $userRepo = $this->em->getRepository(User::class);
        $this->owner = $userRepo->findOneBy(['username' => 'Testuser']);
        $this->otherUser = $userRepo->findOneBy(['username' => 'TestuserNoRef']);

        foreach ($this->em->getRepository(WishlistPlants::class)->findAll() as $w) {
            $this->em->remove($w);
        }
        foreach ($this->em->getRepository(Plants::class)->findAll() as $p) {
            $this->em->remove($p);
        }
        $this->em->flush();
    }


    public function testAddToMyPlantsConvertesWishlistItemIntoPlant(): void
    {
        $wishlist = $this->createWishlistPlant($this->owner, 'Monstera deliciosa', 'Monstera');
        $this->em->persist($wishlist);
        $this->em->flush();
        $wishlistId = $wishlist->getId();

        $this->client->loginUser($this->owner);
        $this->client->request('GET', '/wishlist_plants/add/' . $wishlistId);

        // The controller redirects to the plant edit page
        self::assertResponseRedirects();
        $location = $this->client->getResponse()->headers->get('Location');
        self::assertStringContainsString('/plants/', $location);
        self::assertStringContainsString('/edit', $location);

        // Wishlist item must be gone
        $this->em->clear();
        self::assertNull($this->em->getRepository(WishlistPlants::class)->find($wishlistId));

        // A plant with the same name must now exist
        $plants = $this->em->getRepository(Plants::class)->findBy(['name' => 'Monstera deliciosa']);
        self::assertCount(1, $plants);
    }

    public function testAddToMyPlantsBlockedForOtherUser(): void
    {
        $wishlist = $this->createWishlistPlant($this->owner, 'Stolen Plant', 'Stealicus');
        $this->em->persist($wishlist);
        $this->em->flush();
        $wishlistId = $wishlist->getId();

        $this->client->loginUser($this->otherUser);
        $this->client->request('GET', '/wishlist_plants/add/' . $wishlistId);

        self::assertResponseRedirects('/wishlist_plants');

        // Wishlist item must still exist
        $this->em->clear();
        self::assertNotNull($this->em->getRepository(WishlistPlants::class)->find($wishlistId));
    }

    private function createWishlistPlant(User $user, string $name, string $botanicalName): WishlistPlants
    {
        return (new WishlistPlants())
            ->setName($name)
            ->setBotanicalName($botanicalName)
            ->setDescription('desc')
            ->setQuantity(1)
            ->setUser($user);
    }
}
