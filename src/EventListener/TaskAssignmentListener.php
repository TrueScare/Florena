<?php

namespace App\EventListener;

use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Entity\TaskAssignments;
use App\Repository\NotificationsRepository;
use App\Service\NotificationService;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Common\Collections\Collection;
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
     *
     * @param TaskAssignments $taskAssignment
     * @param LifecycleEventArgs $args
     * @return void
     */
    public function postUpdate(TaskAssignments $taskAssignment, LifecycleEventArgs $args): void
    {
        $changeset = $args->getObjectManager()->getUnitOfWork()->getEntityChangeSet($taskAssignment);

        // nothing to do if there were no changes
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

        // nothing to do if there were no changes
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
        $notifcation = new Notifications()
            ->setCareTask($taskAssignment->getCareTask())
            ->setUser($taskAssignment->getToUser())
            ->setMessage(
                sprintf(
                    $this->messageMapping[Events::postRemove],
                    $taskAssignment->getCareTask()->getTaskType()->value,
                    $taskAssignment->getCareTask()->getPlant()->getName(),
                    $taskAssignment->getFromUser()->getUsername()
                ));

        $this->entityManager->persist($notifcation);
        $this->entityManager->flush();
    }
}
