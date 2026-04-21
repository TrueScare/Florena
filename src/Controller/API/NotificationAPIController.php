<?php

namespace App\Controller\API;

use App\Entity\Notifications;
use App\Repository\NotificationsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[Route('/api/notification')]
#[IsGranted("IS_AUTHENTICATED")]
class NotificationAPIController extends AbstractController
{
    public function __construct(private EntityManagerInterface  $entityManager,
                                private NotificationsRepository $notificationsRepository
    )
    {
    }

    #[Route('/{id}', name: 'app_notification_read', methods: ['GET'])]
    public function read(Notifications $notification)
    {
        if ($notification->getUser() !== $this->getUser()) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        $notification->setIsRead(true);

        $this->entityManager->persist($notification);
        $this->entityManager->flush();

        // requery to get updated information
        $notification = $this->notificationsRepository->find($notification->getId());

        return $this->json([
            'notification' => $notification],
            context: ['groups' => ['notification:read', 'user:ref']]);
    }
}
