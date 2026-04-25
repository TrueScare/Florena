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

    #[Route('/{id}/read', name: 'app_notification_read', methods: ['POST'])]
    public function read(Notifications $notification): Response
    {
        if ($notification->getUser() !== $this->getUser()) {
            return $this->json([], Response::HTTP_FORBIDDEN);
        }

        $notification->setIsRead(true);

        $this->entityManager->flush();

        return $this->json([
            'success' => true,
            'notificationId' => $notification->getId(),
        ]);
    }
}
