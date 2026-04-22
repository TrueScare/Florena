<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\WishlistPlants;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class WishlistPlantsSecurityController extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<WishlistPlants> */
    private EntityRepository $wishlistPlantRepository;

    /** @var EntityRepository<User> */
    private EntityRepository $userRepository;

    private User $owner;
    private User $attacker;
    private WishlistPlants $plant;

    private string $path = '/wishlist_plants/';
    private EntityRepository $plantRepository;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->plantRepository = $this->manager->getRepository(WishlistPlants::class);
        $this->userRepository = $this->manager->getRepository(User::class);

        // Clean up plants from previous runs
        foreach ($this->plantRepository->findAll() as $p) {
            $this->manager->remove($p);
        }
        $this->manager->flush();

        // Fixture users: 'Testuser' is the owner, 'TestuserNoRef' is the attacker
        $this->owner = $this->userRepository->findOneBy(['username' => 'Testuser']);
        $this->attacker = $this->userRepository->findOneBy(['username' => 'TestuserNoRef']);

        $this->plant = $this->createWishlistPlant($this->owner);
        $this->manager->persist($this->plant);
        $this->manager->flush();
    }

    public function testShowRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->plant->getId());

        self::assertResponseRedirects('/wishlist_plants');
    }

    public function testShowAllowedForOwner(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('GET', $this->path . $this->plant->getId());

        self::assertResponseIsSuccessful();
    }

    public function testEditGetRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('GET', $this->path . $this->plant->getId() . '/edit');

        self::assertResponseRedirects('/wishlist_plants');
    }

    public function testEditPostRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->plant->getId() . '/edit');

        self::assertResponseRedirects('/wishlist_plants');

        // Plant name must be unchanged
        $this->manager->refresh($this->plant);
        self::assertSame('TestWishlistPlant', $this->plant->getName());
    }

    public function testDeleteRedirectsForOtherUser(): void
    {
        $this->client->loginUser($this->attacker);
        $this->client->request('POST', $this->path . $this->plant->getId(), [
            '_token' => 'invalid',
        ]);

        self::assertResponseRedirects('/wishlist_plants');

        // Plant must still exist
        self::assertSame(1, $this->plantRepository->count([]));
    }

    public function testDeleteWithInvalidCsrfTokenDoesNotDelete(): void
    {
        $this->client->loginUser($this->owner);
        $this->client->request('POST', $this->path . $this->plant->getId(), [
            '_token' => 'completely_wrong_token',
        ]);

        self::assertResponseRedirects('/wishlist_plants');
        self::assertSame(1, $this->plantRepository->count([]));
    }



    private function createWishlistPlant(User $owner)
    {
        return new WishlistPlants()
            ->setName("TestWishlistPlant")
            ->setBotanicalName("Botanical Name")
            ->setDescription('Description')
            ->setQuantity(5)
            ->setUser($owner);
    }
}
