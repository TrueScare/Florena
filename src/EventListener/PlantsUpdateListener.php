<?php

namespace App\EventListener;

use App\Entity\CareTask;
use App\Entity\Plants;
use App\Enum\CareType;
use App\Service\StressScoreService;
use DateInterval;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postUpdate, entity: Plants::class)]
#[AsEntityListener(event: Events::postPersist, entity: Plants::class)]
#[AsEntityListener(event: Events::postLoad, entity: Plants::class)]
final class PlantsUpdateListener
{
    public function __construct(private EntityManagerInterface $em,
                                private StressScoreService     $stressScoreService)
    {
    }

    public function postUpdate(Plants $plant, LifecycleEventArgs $args): void
    {
        $this->handlePlantsIntervalUpdate($plant, $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($plant));
        $this->em->flush();
    }

    private function handlePlantsIntervalUpdate(Plants $plant, ?array $changeSet): void
    {
        // make sure we have a valid changeset
        $changeSet ??= [
            'watering_interval_days' => [null, $plant->getWateringIntervalDays()],
            'fertilizing_interval_days' => [null, $plant->getFertilizingIntervalDays()],
            'repotting_interval_days' => [null, $plant->getRepottingIntervalDays()],

            'last_watered_at' => [null, $plant->getLastWateredAt()],
            'last_fertilized_at' => [null, $plant->getLastFertilizedAt()],
            'last_repotted_at' => [null, $plant->getLastRepottedAt()],
        ];

        // we have to make sure to only act on changes, that we care about
        $relevantKeys = [
            'watering_interval_days',
            'fertilizing_interval_days',
            'repotting_interval_days',
            'last_watered_at',
            'last_fertilized_at',
            'last_repotted_at',
        ];
        $changeSet = array_filter(
            $changeSet,
            fn($key) => in_array($key, $relevantKeys),
            ARRAY_FILTER_USE_KEY
        );

        // shortcut the cases in which no relevant field was updated/changed
        if (empty($changeSet)) {
            return;
        }

        $tasks = $this->em->getRepository(CareTask::class)->findBy(['plant' => $plant]);
        $taskEval = [];
        foreach ($tasks as $task) {
            $taskEval[$task->getTaskType()->value] = $task;
        }

        if (isset($changeSet['watering_interval_days']) || isset($changeSet['last_watered_at'])) {
            $this->handleCareTaskUpdate(
                task: $taskEval[CareType::water->value] ?? null,
                type: CareType::water,
                plant: $plant,
                interval: $changeSet['watering_interval_days'][1] ?? $plant->getWateringIntervalDays(),
                lastDate: $changeSet['last_watered_at'][1] ?? $plant->getLastWateredAt()
            );
        }

        if (isset($changeSet['fertilizing_interval_days']) || isset($changeSet['last_fertilized_at'])) {
            $this->handleCareTaskUpdate(
                task: $taskEval[CareType::fertilice->value] ?? null,
                type: CareType::fertilice,
                plant: $plant,
                interval: $changeSet['fertilizing_interval_days'][1] ?? $plant->getFertilizingIntervalDays(),
                lastDate: $changeSet['last_fertilized_at'][1] ?? $plant->getLastFertilizedAt()
            );
        }

        if (isset($changeSet['repotting_interval_days']) || isset($changeSet['last_repotted_at'])) {
            $this->handleCareTaskUpdate(
                task: $taskEval[CareType::repot->value] ?? null,
                type: CareType::repot,
                plant: $plant,
                interval: $changeSet['repotting_interval_days'][1] ?? $plant->getRepottingIntervalDays(),
                lastDate: $changeSet['last_repotted_at'][1] ?? $plant->getLastRepottedAt()
            );
        }
    }

    private function handleCareTaskUpdate(?CareTask $task, CareType $type, Plants $plant, ?int $interval = null, ?\DateTimeImmutable $lastDate = null): void
    {
        /*
        adding this for completeness
        if(!isset($taskEval[CareType::water->value] && $changeSet['watering_interval_days'][1] <= 0){
            // there is nothing we have to do in this case
        }
        */

        // remove when interval is at 0 and a task is available
        if ($task && $interval <= 0) {
            $this->em->remove($task);
        }

        // add a new task when the interval is greater than 0 and no task was available yet
        if (!$task && $interval > 0) {
            $task = new CareTask($type, $plant);
        }

        /*
         * at this point we can be sure that we have a care task to work with
         * we still have to check for weither or not we have to act upon the change
         * */
        if ($task && $interval > 0) {
            $task->setDueDate($lastDate->add(DateInterval::createFromDateString($interval . ' day')));
            $this->em->persist($task);
        }
    }

    public function postPersist(Plants $plant, LifecycleEventArgs $args): void
    {
        // this is literally the same as the Update, just without the persist i guess
        $this->handlePlantsIntervalUpdate($plant, null);
        $this->em->flush();
    }

    public function postLoad(Plants $plant, LifecycleEventArgs $args): void
    {
        $plant->setStressScore($this->stressScoreService->calculate($plant));
    }
}
