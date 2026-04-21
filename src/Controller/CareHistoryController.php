<?php

namespace App\Controller;

use App\Entity\Plants;
use App\Repository\PlantsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/care_history')]
#[IsGranted("IS_AUTHENTICATED")]
final class CareHistoryController extends AbstractController
{
    #[Route(name: 'app_care_history_index', methods: ['GET'])]
    public function index(PlantsRepository $plantsRepository): Response
    {
        $plants = $plantsRepository->findHistoryByUser($this->getUser());
        // we only parse plants that do have a history
        // yes, this could also be done via
        array_filter($plants, function (Plants $plant) {
            return $plant->getCareHistory() !== null;
        });

        return $this->render('care_history/index.html.twig', [
            'plants' => $plants,
        ]);
    }
}
