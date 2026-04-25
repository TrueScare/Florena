<?php

namespace App\Service;

use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Entity\PropagationActions;
use App\Entity\User;
use App\Repository\CareTaskRepository;
use App\Repository\NotificationsRepository;
use App\Repository\PropagationActionsRepository;
use Doctrine\ORM\EntityManagerInterface;

final class NotificationService
{
    public function __construct(
        private CareTaskRepository $careTaskRepository,
        private PropagationActionsRepository $propagationActionsRepository,
        private NotificationsRepository $notificationsRepository,
        private EntityManagerInterface $entityManager,
    ) {
    }

    /**
     * Creates missing notifications for due and overdue open work.
     */
    public function createDueNotifications(?User $user = null): int
    {
        $created = 0;

        foreach ($this->careTaskRepository->findAllWithoutNotification($user) as $careTask) {
            $this->entityManager->persist($this->createCareTaskNotification($careTask));
            ++$created;
        }

        foreach ($this->propagationActionsRepository->findAllWithoutNotification($user) as $propagationAction) {
            $this->entityManager->persist($this->createPropagationActionNotification($propagationAction));
            ++$created;
        }

        if ($created > 0) {
            $this->entityManager->flush();
        }

        return $created;
    }

    public function removeReadNotifications(): int
    {
        $readNotifications = $this->notificationsRepository->findBy([
            'is_read' => true,
        ]);

        foreach ($readNotifications as $notification) {
            $this->entityManager->remove($notification);
        }

        if (count($readNotifications) > 0) {
            $this->entityManager->flush();
        }

        return count($readNotifications);
    }

    public function deactivateNotificationsForCareTask(CareTask $careTask): void
    {
        foreach ($careTask->getNotifications() as $notification) {
            $notification->setIsActive(false);
            $notification->setIsRead(true);
        }
    }

    private function createCareTaskNotification(CareTask $careTask): Notifications
    {
        $dueDate = $careTask->getDueDate()?->format('d.m.Y H:i') ?? 'jetzt';

        return (new Notifications())
            ->setMessage(sprintf(
                'Pflegeaufgabe fällig: %s - %s am %s',
                $careTask->getPlant()->getName(),
                $careTask->getTaskType()->value,
                $dueDate
            ))
            ->setCareTask($careTask)
            ->setUser($careTask->getPlant()->getUser());
    }

    private function createPropagationActionNotification(PropagationActions $propagationAction): Notifications
    {
        $plannedDate = $propagationAction->getPlannedDate()?->format('d.m.Y H:i') ?? 'jetzt';

        return (new Notifications())
            ->setMessage(sprintf(
                'Vermehrungsmaßnahme fällig: %s - %s am %s',
                $propagationAction->getPlant()->getName(),
                $propagationAction->getMethod()->value,
                $plannedDate
            ))
            ->setPropagationAction($propagationAction)
            ->setUser($propagationAction->getPlant()->getUser());
    }
}
