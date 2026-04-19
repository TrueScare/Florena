<?php

namespace App\Controller\API;

use App\Entity\CareHistory;
use App\Entity\CareTask;
use App\Enum\CareType;
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
    public function __construct(private EntityManagerInterface $entityManager)
    {
    }

    #[Route('/{id}', name: 'app_care_task_done', methods: ['GET'])]
    public function done(CareTask $careTask): JsonResponse
    {
        if ($careTask->getPlant()->getUser() !== $this->getUser()) {
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
        dump($careHistory->getPerformedAt(), $careTask->getPlant()->getLastWateredAt());

        $this->entityManager->persist($careHistory);
        $this->entityManager->flush();

        return $this->json([
            'care_history' => $careHistory,
            'care_task' => $careTask,
        ]);
    }
}
