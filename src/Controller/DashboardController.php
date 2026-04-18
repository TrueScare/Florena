<?php

namespace App\Controller;

use App\Service\DashboardService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

final class DashboardController extends AbstractController
{
    #[IsGranted('IS_AUTHENTICATED')]
    #[Route('/dashboard', name: 'app_dashboard')]
    public function index(DashboardService $dashboardService): Response
    {
        return $this->render('dashboard/index.html.twig', $dashboardService->getDashboardData($this->getUser()));
    }
}
