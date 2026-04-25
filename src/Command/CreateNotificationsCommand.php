<?php

namespace App\Command;

use App\Entity\CareTask;
use App\Entity\Notifications;
use App\Repository\CareTaskRepository;
use App\Repository\NotificationsRepository;
use App\Repository\PropagationActionsRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:create-notifications',
    description: 'Add a short description for your command',
)]
class CreateNotificationsCommand extends Command
{
    public function __construct(
        private CareTaskRepository      $careTaskRepository,
        private NotificationsRepository $notificationsRepository,
        private PropagationActionsRepository $propagationActionsRepository,
        private EntityManagerInterface  $entityManager,
        private LoggerInterface $logger
    )
    {
        parent::__construct();
    }

    protected function configure(): void
    {

    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Cleanup and Setup Notifications');
        $countRemovedNotifications = $this->cleanupNotifications();
        $io->info("[ " . $countRemovedNotifications . " ] read Notifications removed");
        $this->logger->info("[ " . $countRemovedNotifications . " ] read Notifications removed");

        $careTasks = $this->careTaskRepository->findAllWithoutNotification();
        $io->info("[ " . count($careTasks) . " ] Tasks found without a notification");
        $this->logger->info("[ " . count($careTasks) . " ] Tasks found without a notifications");

        /** @var CareTask $careTask */
        foreach($careTasks as $careTask) {
            $notification = new Notifications();
            $notification->setMessage("Aufgabe fällig! " . $careTask->getPlant()->getName() . " " . $careTask->getTaskType()->value);
            $notification->setCareTask($careTask);
            $notification->setUser($careTask->getPlant()->getUser());

            $this->entityManager->persist($notification);
        }

        $propagationActions = $this->propagationActionsRepository->findAllWithoutNotification();
        $io->info("[ " . count($propagationActions) . " ] PropagationActions found without a notification");
        $this->logger->info("[ " . count($propagationActions) . " ] PropagationActions found without a notifications");

        foreach($propagationActions as $propagationAction) {
            $notification = new Notifications()
                ->setMessage("Vermehrungsmaßnahmen fällig! " . $careTask->getPlant()->getName() . " " . $careTask->getTaskType()->value)
                ->setPropagationAction($propagationAction)
                ->setUser($propagationAction->getPlant()->getUser());

            $this->entityManager->persist($notification);
        }

        $this->entityManager->flush();

        $io->success("[ " . count($careTasks) . " ] Notifications added");
        $this->logger->info("[ " . count($careTasks) . " ] Notifications added");

        return Command::SUCCESS;
    }

    private function cleanupNotifications()
    {
        $readNotifications = $this->notificationsRepository->findBy([
            'is_read' => true
        ]);

        foreach ($readNotifications as $notification) {
            $this->entityManager->remove($notification);
        }

        return count($readNotifications);
    }
}
