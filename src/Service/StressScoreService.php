<?php

namespace App\Service;

use App\Entity\CareTask;
use App\Entity\Plants;
use App\Enum\CareType;
use App\Enum\StressScoreStatus;

class StressScoreService
{
    private const array WEIGHTS = [
        CareType::water->value => 0.5,
        CareType::fertilice->value => 0.4,
        CareType::repot->value => 0.1
    ];

    public function calculate(Plants $plant): float
    {
        $tasks = $plant->getCareTasks();
        $scores = [];

        foreach ($tasks as $task) {
            switch ($task->getTaskType()) {
                case CareType::water:
                    $scores[$task->getTaskType()->value] = $this->singleTaskScore($task, $plant->getWateringIntervalDays());
                    break;
                case CareType::fertilice:
                    $scores[$task->getTaskType()->value] = $this->singleTaskScore($task, $plant->getFertilizingIntervalDays());
                    break;
                case CareType::repot:
                    $scores[$task->getTaskType()->value] = $this->singleTaskScore($task, $plant->getRepottingIntervalDays());
                    break;
            }
        }

        // Gewichtetes geometrisches Mittel
        $geoMean = 1.0;

        foreach (self::WEIGHTS as $type => $exponent) {
            $score = $scores[$type] ?? 1.0;
            $geoMean *= $score ** $exponent;
        }

        return round($geoMean * 100, 2);
    }

    public function mapScore(float $score)
    {
        return match (true) {
            $score >= 80 => StressScoreStatus::healthy,
            $score >= 50 => StressScoreStatus::slightlyStressed,
            default => StressScoreStatus::problem,
        };
    }

    private function singleTaskScore(CareTask $careTask, int $interval): float
    {
        $dueDate = $careTask->getDueDate()->setTime(0, 0);
        $now = new \DateTimeImmutable('today');

        // Noch nicht überfällig
        if ($dueDate >= $now) {
            return 1.0;
        }

        $decayDays = $interval * 2;
        $overdueDays = (int)$now->diff($dueDate)->days;
        $ratio = $overdueDays / $decayDays;

        return max(0.0, 1.0 - ($ratio ** 2));
    }
}
