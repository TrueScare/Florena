<?php

namespace App\Controller;

use App\Entity\CareTask;
use App\Entity\TaskAssignments;
use App\EventListener\TaskAssignmentListener;
use App\Form\TaskAssignmentsType;
use App\Repository\TaskAssignmentsRepository;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\Migrations\Configuration\EntityManager\ManagerRegistryEntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/task_assignments')]
#[IsGranted("IS_AUTHENTICATED")]
final class TaskAssignmentsController extends AbstractController
{
    public function __construct(private readonly TaskAssignmentListener $taskAssignmentListener)
    {
    }

    #[Route(name: 'app_task_assignments_index', methods: ['GET'])]
    public function index(TaskAssignmentsRepository $taskAssignmentsRepository): Response
    {
        return $this->render('task_assignments/index.html.twig', [
            'task_assignments' => $taskAssignmentsRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_task_assignments_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, TaskAssignmentsRepository $taskAssignmentsRepository): Response
    {
        $taskAssignment = new TaskAssignments();
        $form = $this->createForm(TaskAssignmentsType::class, $taskAssignment, ['user' => $this->getUser()]);

        $form->handleRequest($request);
        $taskAssignment->setFromUser($this->getUser());

        if ($form->isSubmitted() && $form->isValid()) {

            $tasks = $form->get('care_tasks')->getData();
            $dbTasks = $taskAssignmentsRepository->findBy(['to_user' => $taskAssignment->getToUser(), 'care_task' => array_map(fn(CareTask $task) => $task->getId(), $tasks->toArray())]);

            /** @var CareTask $task */
            foreach ($tasks as $task) {
                if (in_array($task->getId(), array_map(fn(TaskAssignments $task) => $task->getCareTask()->getId(), $dbTasks))) {
                    $this->addFlash('error', sprintf("Die Aufgabe '%s %s' ist bereits an User '%s' vergeben.", $task->getPlant()->getName(), $task->getTaskType()->value, $taskAssignment->getToUser()->getUsername()));
                    continue;
                }
                // create an assignment for every task selected in the form
                // while using the formdata as a template
                $newTaskAssignment = new TaskAssignments()
                    ->setCareTask($task)
                    ->setToUser($taskAssignment->getToUser())
                    ->setFromUser($taskAssignment->getFromUser())
                    ->setStartDate($taskAssignment->getStartDate())
                    ->setEndDate($taskAssignment->getEndDate())
                    ->setAssignedAt($taskAssignment->getAssignedAt());
                $entityManager->persist($newTaskAssignment);
                $this->addFlash("success", sprintf("Die Aufgabe '%s %s' wurde an User '%s' vergeben.", $task->getPlant()->getName(), $task->getTaskType()->value, $taskAssignment->getToUser()->getUsername()));
            }
            $entityManager->flush();

            return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task_assignments/new.html.twig', [
            'task_assignment' => $taskAssignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_assignments_show', methods: ['GET'])]
    public function show(TaskAssignments $taskAssignment): Response
    {
        // we have to check if the user is either the receiving or assigning part
        // both should be able to view the task assignment
        if ($taskAssignment->getToUser() !== $this->getUser() && $taskAssignment->getFromUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task_assignments/show.html.twig', [
            'task_assignment' => $taskAssignment,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_task_assignments_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, TaskAssignments $taskAssignment, EntityManagerInterface $entityManager, TaskAssignmentsRepository $taskAssignmentsRepository): Response
    {
        // only the assigning user should be able to edit the assignment
        if ($taskAssignment->getFromUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
        }

        $form = $this->createForm(TaskAssignmentsType::class, $taskAssignment, ['user' => $this->getUser()]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($taskAssignment);
            $entityManager->flush();

            return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->render('task_assignments/edit.html.twig', [
            'task_assignment' => $taskAssignment,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_task_assignments_delete', methods: ['POST'])]
    public function delete(Request $request, TaskAssignments $taskAssignment, EntityManagerInterface $entityManager): Response
    {
        // either the assignment was faulty or not intended
        // or the receiver denied the assignment
        // both need to be able to delete the assignment anyway
        if ($taskAssignment->getToUser() !== $this->getUser() && $taskAssignment->getFromUser() !== $this->getUser()) {
            return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
        }

        if ($this->isCsrfTokenValid('delete' . $taskAssignment->getId(), $request->getPayload()->getString('_token'))) {
            $entityManager->remove($taskAssignment);
            $entityManager->flush();
        }

        return $this->redirectToRoute('app_task_assignments_index', [], Response::HTTP_SEE_OTHER);
    }
}
