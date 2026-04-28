<?php

namespace App\EventListener;

use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Entity\TaskAssignments;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postUpdate, entity: TaskAssignments::class)]
#[AsEntityListener(event: Events::postPersist, entity: TaskAssignments::class)]
#[AsEntityListener(event: Events::postRemove, entity: TaskAssignments::class)]
final class TaskAssignmentListener
{
    private array $messageMapping = [
        Events::postUpdate => 'Eine Aufgabenzuweisung hat sich geändert.',
        Events::postPersist => 'Dir wurde eine neue Aufgabe zugewiesen.',
        Events::postRemove => 'Die Aufgabe "%s" für die Pflanze "%s" von "%s" muss nicht mehr von dir erledigt werden.'
    ];

    public function __construct(
        private EntityManagerInterface $entityManager,
    )
    {
    }

    /**
     * Create a notification if a TaskAssignment was edited
     */
    public function postUpdate(TaskAssignments $taskAssignment, LifecycleEventArgs $args): void
    {
        $changeset = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($taskAssignment);

        if (count($changeset) <= 0) {
            return;
        }

        $this->entityManager->persist(new Notifications()
            ->setCareTask($taskAssignment->getCareTask())
            ->setUser($taskAssignment->getToUser())
            ->setMessage($this->messageMapping[Events::postUpdate])
        );
    }

    public function postPersist(TaskAssignments $taskAssignment, LifecycleEventArgs $args): void
    {
        $changeset = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($taskAssignment);

        if (count($changeset) <= 0) {
            return;
        }

        $this->entityManager->persist(new Notifications()
            ->setCareTask($taskAssignment->getCareTask())
            ->setUser($taskAssignment->getToUser())
            ->setMessage($this->messageMapping[Events::postPersist])
        );
    }

    public function postRemove(TaskAssignments $taskAssignment): void
    {
        $careTask = $taskAssignment->getCareTask();

        if ($careTask === null) {
            return;
        }

        // The CareTask may be detached or scheduled for deletion (e.g. when the parent
        // Plant is cascade-deleted). In that case there is nothing meaningful to notify about.
        $uow = $this->entityManager->getUnitOfWork();
        if ($uow->isScheduledForDelete($careTask) || !$uow->isInIdentityMap($careTask)) {
            return;
        }

        // Re-fetch from the DB to guarantee a managed instance before persisting the Notification.
        $managedCareTask = $this->entityManager->find(CareTask::class, $careTask->getId());
        if ($managedCareTask === null) {
            return;
        }

        $notification = new Notifications();
        $notification->setCareTask($managedCareTask);
        $notification->setUser($taskAssignment->getToUser());
        $notification->setMessage(sprintf(
            $this->messageMapping[Events::postRemove],
            $managedCareTask->getTaskType()->value,
            $managedCareTask->getPlant()->getName(),
            $taskAssignment->getFromUser()->getUsername()
        ));

        $this->entityManager->persist($notification);
        $this->entityManager->flush();
    }
}
