<?php

namespace App\Controller;

use App\Entity\CareTask;
use App\Form\CareTaskType;
use App\Repository\CareTaskRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/care_task')]
final class CareTaskController extends AbstractController
{
    #[Route(name: 'app_care_task_index', methods: ['GET'])]
    public function index(CareTaskRepository $careTaskRepository): Response
    {
        return $this->render('care_task/index.html.twig', [
            'care_tasks' => $careTaskRepository->findAllByUser($this->getUser()),
        ]);
    }

    #[Route('/{id}', name: 'app_care_task_show', methods: ['GET'])]
    public function show(CareTask $careTask): Response
    {
        return $this->render('care_task/show.html.twig', [
            'care_task' => $careTask,
        ]);
    }
}
