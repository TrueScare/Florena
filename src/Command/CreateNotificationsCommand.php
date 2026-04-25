<?php

namespace App\Command;

use App\Service\NotificationService;
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
        private LoggerInterface $logger,
        private NotificationService $notificationService,
    ) {
        parent::__construct();
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title('Cleanup and Setup Notifications');
        $countRemovedNotifications = $this->notificationService->removeReadNotifications();
        $io->info("[ " . $countRemovedNotifications . " ] read Notifications removed");
        $this->logger->info("[ " . $countRemovedNotifications . " ] read Notifications removed");

        $createdNotifications = $this->notificationService->createDueNotifications();

        $io->success("[ " . $createdNotifications . " ] Notifications added");
        $this->logger->info("[ " . $createdNotifications . " ] Notifications added");

        return Command::SUCCESS;
    }
}
