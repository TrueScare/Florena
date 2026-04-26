<?php

namespace App\Controller;

use App\Repository\NotificationsRepository;
use App\Service\NotificationService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

#[IsGranted('IS_AUTHENTICATED')]
final class NotificationsController extends AbstractController
{
    public function __construct(
        private NotificationsRepository $notificationsRepository,
        private NotificationService $notificationService,
    ) {
    }

    #[Route('/notifications', name: 'app_notifications', methods: ['GET'])]
    public function index(): Response
    {
        $this->notificationService->createDueNotifications($this->getUser());

        $notifications = $this->notificationsRepository->findUnreadActiveByUser($this->getUser());

        return $this->render('notifications/_list.html.twig', [
            'notifications' => $notifications,
        ]);
    }

    #[Route('/notifications/count', name: 'app_notifications_count', methods: ['GET'])]
    public function count(): Response
    {
        return $this->json([
            'count' => $this->notificationsRepository->countUnreadActiveByUser($this -> getUser()),
        ]);
    }
}
