<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class ImpressumController extends AbstractController
{
    #[Route('/impressum', name: 'app_impressum')]
    public function index(): Response
    {
        return $this->render('impressum/index.html.twig', [
            'controller_name' => 'ImpressumController',
        ]);
    }
}
