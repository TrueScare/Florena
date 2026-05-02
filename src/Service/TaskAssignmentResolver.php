<?php

namespace App\Service;

use App\Entity\CareTask;
use App\Entity\TaskAssignments;
use App\Entity\User;
use App\Repository\TaskAssignmentsRepository;

final class TaskAssignmentResolver
{
    public function __construct(
        private TaskAssignmentsRepository $taskAssignmentsRepository,
    ) {
    }

    public function findActiveForTask(CareTask $careTask): ?TaskAssignments
    {
        foreach ($this->taskAssignmentsRepository->findBy(['care_task' => $careTask]) as $assignment) {
            if ($this->coversTask($assignment, $careTask)) {
                return $assignment;
            }
        }

        return null;
    }

    public function findActiveForTaskAndUser(CareTask $careTask, User $user): ?TaskAssignments
    {
        foreach ($this->taskAssignmentsRepository->findBy(['care_task' => $careTask, 'to_user' => $user]) as $assignment) {
            if ($this->coversTask($assignment, $careTask)) {
                return $assignment;
            }
        }

        return null;
    }

    public function coversTask(TaskAssignments $assignment, CareTask $careTask): bool
    {
        return $careTask->getDueDate() >= $assignment->getStartDate()
            && $careTask->getDueDate() <= $assignment->getEndDate()->modify('+59 seconds');
    }
}
