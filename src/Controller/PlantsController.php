<?php

namespace App\Controller;

use App\Entity\Plants;
use App\Entity\WishlistPlants;
use App\Form\PlantsType;
use App\Repository\LocationsRepository;
use App\Repository\PlantsRepository;
use App\Repository\WishlistPlantsRepository;
use App\Service\Fitness\FitnessService;
use App\Service\Pagination\PaginationService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/plants')]
#[IsGranted("IS_AUTHENTICATED")]
final class PlantsController extends AbstractController
{
    public function __construct(private FitnessService           $fitnessService,
                                private LocationsRepository      $locationsRepository,
                                private WishlistPlantsRepository $wishlistPlantsRepository)
    {
    }

    #[Route(name: 'app_plants_index', methods: ['GET'])]
    public function index(Request $request, PlantsRepository $plantsRepository, PaginationService $paginationService): Response
    {
        $pageInfo = $paginationService->getPageInfoFromRequest($request);
        $queryBuilder = $plantsRepository->getQueryBuilderFindAllByUser($this->getUser(), $pageInfo->getOrder(), $pageInfo->getSearchTerm());
        $paginationResult = $paginationService->paginate($queryBuilder, $pageInfo->getPage(), $pageInfo->getLimit(), $pageInfo->getOrder(), $pageInfo->getSearchTerm());

        return $this->render('plants/index.html.twig', [
            'paginationResult' => $paginationResult,
            'plants' => $paginationResult->getItems(),
        ]);
    }

    #[Route('/new', name: 'app_plants_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, string $uploadsPath): Response
    {
        $plant = $this->getPlantFromRequestOrDefault($request);

        $form = $this->createForm(PlantsType::class, $plant, [
            'user' => $this->getUser(),
            'wishlist_plant' => $request->query->all('plants')['wishlist_plant'] ?? null,
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();
            if ($uploadedFile) {
                $destination = $uploadsPath . '/plant_images';
                $newFileName = uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move($destination, $newFileName);

                $plant->setPhotoPath($newFileName);
            }

            $wishlistId = $form->get('wishlist_plant')?->getData();
            if ($wishlistId && $wishlistPlant = $this->wishlistPlantsRepository->find($wishlistId)) {
                $entityManager->remove($wishlistPlant);
                $this->addFlash('success', "Wunschpflanze wurde übernommen.");
            }

            $entityManager->persist($plant);
            $entityManager->flush();
            $this->addFlash('success', 'Deine Pflanze "' . $plant->getName() . '" wurde erfolgreich angelegt.');
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plants/new.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plants_show', methods: ['GET'])]
    public function show(Plants $plant): Response
    {
        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($plant->getLocation()) {
            $fitnessStatus = $this->fitnessService->checkFitForPlantInLocation(
                $plant->getLightRequirement(),
                $plant->getTemperatureRequirement(),
                $plant->getHumidityRequirement(),
                $plant->getLocation());
        }

        return $this->render('plants/show.html.twig', [
            'plant' => $plant,
            'fitnessStatus' => $fitnessStatus ?? null,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_plants_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Plants $plant, EntityManagerInterface $entityManager, string $uploadsPath): Response
    {

        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $currentImagePath = $uploadsPath . '/plant_images/' . $plant->getPhotoPath();

        $form = $this->createForm(PlantsType::class, $plant, [
            'user' => $this->getUser()
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form->get('image')->getData();

            if ($uploadedFile) {
                if ($currentImagePath) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($currentImagePath);
                }
                $destination = $uploadsPath . '/plant_images';
                $newFileName = uniqid() . '.' . $uploadedFile->guessExtension();

                $uploadedFile->move($destination, $newFileName);

                $plant->setPhotoPath($newFileName);
            }

            $entityManager->flush();

            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('plants/edit.html.twig', [
            'plant' => $plant,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_plants_delete', methods: ['POST'])]
    public function delete(Request $request, Plants $plant, EntityManagerInterface $entityManager): Response
    {
        //make sure that we only show plants to the owner of the plant
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $plant->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($plant);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/add/{id}', name: 'app_plants_add', methods: ['GET'])]
    public function addToWishlist(Plants $plant, EntityManagerInterface $entityManager): Response
    {
        if ($this->getUser() !== $plant->getUser()) {
            return $this->redirectToRoute('app_plants_index', [], Response::HTTP_SEE_OTHER);
        }

        $wishlistPlant = new WishlistPlants()
            ->setName($plant->getName())
            ->setDescription($plant->getDescription() ?? '')
            ->setBotanicalName($plant->getBotanicalName() ?? '')
            ->setUser($plant->getUser());

        if ($plant->getLocation()) {
            $wishlistPlant->setLocation($plant->getLocation());
        }

        $entityManager->persist($wishlistPlant);
        $entityManager->flush();

        return $this->redirectToRoute('app_wishlist_plants_show', ['id' => $wishlistPlant->getId()], Response::HTTP_SEE_OTHER);
    }

    private function getPlantFromRequestOrDefault(Request $request): Plants
    {
        $plantsQuery = $request->query->all('plants');

        $plant = new Plants()
            ->setName($plantsQuery['name'] ?? '')
            ->setDescription($plantsQuery['description'] ?? '')
            ->setBotanicalName($plantsQuery['botanical_name'] ?? '');
        if ($plantsQuery['location'] ?? null) {
            $plant->setLocation($this->locationsRepository->find($plantsQuery['location']));
        }

        return $plant;
    }
}
