<?php

namespace App\Controller;

use App\Entity\CareTask;
use App\Repository\CareTaskRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/care_task')]
#[IsGranted("IS_AUTHENTICATED")]
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
        if ($careTask->getPlant()->getUser() !== $this->getUser()) {
        return $this->redirectToRoute('app_care_task_index',[], Response::HTTP_SEE_OTHER);
        }
        return $this->render('care_task/show.html.twig', [
            'care_task' => $careTask,
        ]);
    }
}
