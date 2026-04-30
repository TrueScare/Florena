<?php

namespace App\Controller;

use App\Entity\Plants;
use App\Entity\WishlistPlants;
use App\Enum\HumidityRequirement;
use App\Enum\LightRequirement;
use App\Enum\TemperatureRequirement;
use App\Form\WishlistPlantsType;
use App\Repository\WishlistPlantsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/wishlist_plants')]
#[IsGranted("IS_AUTHENTICATED")]
class WishlistPlantsController extends AbstractController
{
    #[Route(name: 'app_wishlist_plants_index', methods: ['GET'])]
    public function index(WishlistPlantsRepository $wishlistPlantsRepository): Response
    {
        return $this->render('wishlist_plants/index.html.twig', [
            'wishlist_plants' => $wishlistPlantsRepository->findAllByUser($this->getUser()),
        ]);
    }

    #[Route('/new', name: 'app_wishlist_plants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager): Response
    {
        $wishlistPlant = new WishlistPlants();
        $form = $this->createForm(WishlistPlantsType::class, $wishlistPlant, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        $wishlistPlant->setUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($wishlistPlant);
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist_plants/new.html.twig', [
            'wishlist_plant' => $wishlistPlant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wishlist_plants_show', methods: ['GET'])]
    public function show(WishlistPlants $wishlistPlant): Response
    {
        if ($this->getUser() !== $wishlistPlant->getUser()) {
            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist_plants/show.html.twig', [
            'wishlist_plant' => $wishlistPlant,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_wishlist_plants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, WishlistPlants $wishlistPlant, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $wishlistPlant->getUser()) {
            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(WishlistPlantsType::class, $wishlistPlant, [
            'user' => $this->getUser(),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('wishlist_plants/edit.html.twig', [
            'wishlist_plant' => $wishlistPlant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_wishlist_plants_delete', methods: ['POST'])]
    public function delete(Request $request, WishlistPlants $wishlistPlant, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $wishlistPlant->getUser()) {
            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $wishlistPlant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($wishlistPlant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/add/{id}', name: 'app_wishlist_plants_add', methods: ['GET'])]
    public function addToMyPlants(WishlistPlants $wishlistPlant, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $wishlistPlant->getUser()) {
            return $this->redirectToRoute('app_wishlist_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->redirectToRoute('app_plants_new', [
            'plants[name]' => $wishlistPlant->getName(),
            'plants[botanical_name]' => $wishlistPlant->getBotanicalName(),
            'plants[description]' => $wishlistPlant->getDescription(),
            'plants[location]' => $wishlistPlant->getLocation()?->getId(),
            'plants[wishlist_plant]' => $wishlistPlant->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
