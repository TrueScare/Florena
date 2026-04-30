<?php

namespace App\Tests\Controller;

use App\Entity\User;
use App\Entity\WishlistPlants;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class WishlistPlantsControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;

    /** @var EntityRepository<WishlistPlants> */
    private EntityRepository $wishlistPlantRepository;
    private string $path = '/wishlist_plants/';
    private User $user;

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->wishlistPlantRepository = $this->manager->getRepository(WishlistPlants::class);
        $this->user = $this->manager->getRepository(User::class)->findOneBy(['username' => 'Testuser']);

        foreach ($this->wishlistPlantRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->loginUser($this->user);
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Wunschpflanze');
    }

    public function testNew(): void
    {
        $this->client->loginUser($this->user);
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Speichern', [
            'wishlist_plants[name]' => "Testing",
            'wishlist_plants[description]' => 'Testing',
            'wishlist_plants[botanical_name]' => 'Testing',
        ]);

        self::assertResponseRedirects('/wishlist_plants');

        self::assertSame(1, $this->wishlistPlantRepository->count([]));
    }

    public function testShow(): void
    {
        $this->client->loginUser($this->user);
        $fixture = $this->createWishlistPlant($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('WishlistPlant');
    }

    public function testEdit(): void
    {
        $this->client->loginUser($this->user);
        $fixture = $this->createWishlistPlant($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Bearbeiten', [
            'wishlist_plants[name]' => 'Something New',
            'wishlist_plants[description]' => 'Something New',
            'wishlist_plants[botanical_name]' => 'Something New',
        ]);

        self::assertResponseRedirects('/wishlist_plants');

        $fixture = $this->wishlistPlantRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getName());
        self::assertSame('Something New', $fixture[0]->getDescription());
        self::assertSame('Something New', $fixture[0]->getBotanicalName());
    }

    public function testRemove(): void
    {
        $this->client->loginUser($this->user);
        $fixture = $this->createWishlistPlant($this->user);

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Löschen');

        self::assertResponseRedirects('/wishlist_plants');
        self::assertSame(0, $this->wishlistPlantRepository->count([]));
    }

    private function createWishlistPlant(User $user): WishlistPlants
    {
        return new WishlistPlants()
            ->setName("TestWishlistPlant")
            ->setBotanicalName("Botanical Name")
            ->setDescription('Description')
            ->setQuantity(5)
            ->setUser($user);
    }
}
