<?php

namespace App\Controller\API;

use App\Entity\CareHistory;
use App\Entity\CareTask;
use App\Entity\TaskAssignments;
use App\Enum\CareType;
use App\Repository\TaskAssignmentsRepository;
use App\Service\NotificationService;
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
    )
    {
    }

    #[Route('/{id}', name: 'app_care_task_done', methods: ['GET'])]
    public function done(CareTask $careTask, TaskAssignmentsRepository $taskAssignmentsRepository): JsonResponse
    {
        // this statement checkt weither or not the user is owner of the task
        // or the user is anywhere assigned to the task
        $currentUserId = $this->getUser()->getId();
        $isOwner = $careTask->getPlant()->getUser()->getId() === $currentUserId;
        $isAssigned = false;
        $count = $taskAssignmentsRepository->countTaskAssignmentsForCareTaskByUser($careTask, $this->getUser());
        if(!$isOwner){
            $isAssigned = $count >0;
        }

        if (!$isOwner && !$isAssigned) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        $careHistory = new CareHistory();
        $careHistory->setUser($this->getUser());
        $careHistory->setPlant($careTask->getPlant());
        $careHistory->setCareType($careTask->getTaskType());

        match ($careTask->getTaskType()) {
            CareType::water => $careTask->getPlant()->setLastWateredAt($careHistory->getPerformedAt()),
            CareType::fertilice => $careTask->getPlant()->setLastFertilizedAt($careHistory->getPerformedAt()),
            CareType::repot => $careTask->getPlant()->setLastRepottedAt($careHistory->getPerformedAt())
        };

        $this->notificationService->deactivateNotificationsForCareTask($careTask);

        $this->entityManager->persist($careHistory);
        $this->entityManager->flush();

        return $this->json([
            'care_history' => $careHistory,
            'care_task' => $careTask,
        ], context: ['groups' => ['care_task:read', 'care_history:read', 'plant:ref']]);
    }
}
