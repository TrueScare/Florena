<?php

namespace App\Controller;

use App\Entity\CareTask;
use App\Enum\CalenderTimeInterval;
use App\Repository\CareTaskRepository;
use App\Service\TaskAssignmentResolver;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/care_task')]
#[IsGranted("IS_AUTHENTICATED")]
final class CareTaskController extends AbstractController
{
    public function __construct(
        private TaskAssignmentResolver $taskAssignmentResolver,
    ) {
    }

    #[Route(name: 'app_care_task_index', methods: ['GET'])]
    public function index(Request $request, CareTaskRepository $careTaskRepository): Response
    {
        $interval = CalenderTimeInterval::tryFrom($request->query->get('interval', CalenderTimeInterval::week->value));
        $interval ??= CalenderTimeInterval::week;

        $ownTasks = $careTaskRepository->findAllByUserInInterval($this->getUser(), $interval);
        $assignedTasks = array_values(array_filter(
            $careTaskRepository->findAllAssignedToUserInInterval($this->getUser(), $interval),
            fn (CareTask $careTask) => $this->taskAssignmentResolver->findActiveForTaskAndUser($careTask, $this->getUser()) !== null
        ));
        $ownTaskItems = array_map(fn (CareTask $careTask) => $this->buildTaskItem($careTask, false), $ownTasks);
        $receivedTaskItems = array_map(fn (CareTask $careTask) => $this->buildTaskItem($careTask, true), $assignedTasks);

        return $this->render('care_task/index.html.twig', [
            'care_tasks' => $ownTasks,
            'assigned_tasks' => $assignedTasks,
            'care_task_items' => array_merge($ownTaskItems, $receivedTaskItems),
            'received_task_items' => $receivedTaskItems,
            'interval' => $interval->value,
        ]);
    }

    private function buildTaskItem(CareTask $careTask, bool $received): array
    {
        $assignment = $received
            ? $this->taskAssignmentResolver->findActiveForTaskAndUser($careTask, $this->getUser())
            : $this->taskAssignmentResolver->findActiveForTask($careTask);
        $currentResponsible = $assignment?->getToUser() ?? $careTask->getPlant()->getUser();

        return [
            'task' => $careTask,
            'assignment' => $assignment,
            'received' => $received,
            'currentResponsible' => $currentResponsible,
            'originalResponsible' => $careTask->getPlant()->getUser(),
            'canComplete' => $currentResponsible === $this->getUser(),
        ];
    }

}
