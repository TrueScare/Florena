<?php

namespace App\Service;

use App\Entity\CareHistory;
use App\Entity\CareTask;
use App\Entity\User;
use App\Repository\CareHistoryRepository;

class PointService
{
    public const BASE_POINTS = 5;
    public CONST BONUS_MULITPLIER = 2;
    public function __construct(private CareHistoryRepository $careHistoryRepository)
    {
    }

    public function calculate(User $user): int
    {
        $doneTasks = $this->careHistoryRepository->findBy([
           'user' => $user,
        ]);

        $ownTasks = array_filter($doneTasks, function (CareHistory $task) use ($user) {
           return $task->getPlant()->getUser() === $user;
        });
        $assignedTasks = array_filter($doneTasks, function (CareHistory $task) use ($user) {
            return $task->getPlant()->getUser() !== $user;
        });

        $score = 0;
        $score += $ownTasks ? count($ownTasks) * self::BASE_POINTS : 0;
        $score += $assignedTasks ? count($assignedTasks) * self::BASE_POINTS  * self::BONUS_MULITPLIER : 0;

        return $score;
    }
}
