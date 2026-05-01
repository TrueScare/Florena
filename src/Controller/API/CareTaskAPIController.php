<?php

namespace App\Controller\API;

use App\Entity\CareHistory;
use App\Entity\CareTask;
use App\Enum\CareType;
use App\Service\NotificationService;
use App\Service\TaskAssignmentResolver;
use DateInterval;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/care_task')]
#[IsGranted("IS_AUTHENTICATED")]
class CareTaskAPIController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager,
        private NotificationService    $notificationService,
        private TaskAssignmentResolver $taskAssignmentResolver,
    )
    {
    }

    #[Route('/{id}', name: 'app_care_task_done', methods: ['GET'])]
    public function done(CareTask $careTask): JsonResponse
    {
        $currentUserId = $this->getUser()->getId();
        $isOwner = $careTask->getPlant()->getUser()->getId() === $currentUserId;
        $activeAssignment = $this->taskAssignmentResolver->findActiveForTask($careTask);
        $isAssigned = $activeAssignment?->getToUser()?->getId() === $currentUserId;

        if ($activeAssignment !== null && !$isAssigned) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        if ($activeAssignment === null && !$isOwner) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        $careHistory = new CareHistory();
        $careHistory->setUser($this->getUser());
        $careHistory->setPlant($careTask->getPlant());
        $careHistory->setCareType($careTask->getTaskType());

        $plant = $careTask->getPlant();
        $previousDueDate = $careTask->getDueDate();
        $performedAt = $careHistory->getPerformedAt();
        $nextDueDate = null;
        $intervalDays = match ($careTask->getTaskType()) {
            CareType::water => $plant->setLastWateredAt($performedAt)->getWateringIntervalDays(),
            CareType::fertilice => $plant->setLastFertilizedAt($performedAt)->getFertilizingIntervalDays(),
            CareType::repot => $plant->setLastRepottedAt($performedAt)->getRepottingIntervalDays(),
        };

        if ($intervalDays !== null && $intervalDays > 0) {
            $nextDueDateBase = $previousDueDate > $performedAt ? $previousDueDate : $performedAt;
            $nextDueDate = $nextDueDateBase->add(DateInterval::createFromDateString($intervalDays . ' day'));
        }

        $this->notificationService->deactivateNotificationsForCareTask($careTask);

        $this->entityManager->persist($careHistory);
        $this->entityManager->flush();

        if ($nextDueDate !== null) {
            $careTask->setDueDate($nextDueDate);
            $this->entityManager->persist($careTask);
            $this->entityManager->flush();
        }

        return $this->json([
            'care_history' => $careHistory,
            'care_task' => $careTask,
        ], context: ['groups' => ['care_task:read', 'care_history:read', 'plant:ref']]);
    }

}
