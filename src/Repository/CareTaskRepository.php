<?php

namespace App\Repository;

use App\Entity\CareTask;
use App\Enum\CalenderTimeInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query\Expr\Join;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<CareTask>
 */
class CareTaskRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, CareTask::class);
    }

    public function findAllByUser(UserInterface $user): array
    {
        return $this->getBaseUserQueryBuilder($user)
            ->getQuery()
            ->getResult();
    }

    /**
     * Get CareTasks that are due in a certain interval.
     *
     * CareTasks are designed to only contain open Tasks. Everything else is in the CareHistory.
     * That in mind, we only have to set the EndDate by which we want to filter.
     * That way we always track the overdue Tasks.
     *
     * @param UserInterface $user
     * @param CalenderTimeInterval $interval
     * @return array
     *
     */
    public function findAllByUserInInterval(UserInterface $user, CalenderTimeInterval $interval = CalenderTimeInterval::week): array
    {
        match ($interval) {
            CalenderTimeInterval::day => $endDate = new DateTimeImmutable("tomorrow"),
            CalenderTimeInterval::week => $endDate = new DateTimeImmutable("last day of this week")->modify("+1 day"),
            CalenderTimeInterval::month => $endDate = new DateTimeImmutable("last day of this month")->modify("+1 day")
        };

        return $this->getBaseUserQueryBuilder($user)
            ->andWhere('c.due_date < :endDate')
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }


    /**
     * Get all CareTasks that do not have a notification
     *
     * @return array
     */
    public function findAllWithoutNotification(?UserInterface $user = null): array
    {
        $endOfDay = new DateTimeImmutable("tomorrow");

        $queryBuilder = $this->createQueryBuilder('c')
            ->leftJoin('c.plant', 'p')
            ->leftJoin('c.notifications', 'n', 'WITH', 'n.is_read = 0')
            ->addSelect('n')
            ->addSelect('p')
            ->andWhere('n IS NULL')
            ->andWhere('c.due_date < :endDate')
            ->setParameter('endDate', $endOfDay);

        if ($user !== null) {
            $queryBuilder
                ->andWhere('p.user = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    public function findAllAssignedToUserInInterval(?UserInterface $getUser, CalenderTimeInterval $interval)
    {
        $endDate = match ($interval) {
            CalenderTimeInterval::day => new DateTimeImmutable("tomorrow"),
            CalenderTimeInterval::week => new DateTimeImmutable("last day of this week")->modify("+1 day"),
            CalenderTimeInterval::month => new DateTimeImmutable("last day of this month")->modify("+1 day")
        };

        return $this->createQueryBuilder('c')
            ->addSelect('ta', 'u')
            ->leftJoin('c.taskAssignments', 'ta')
            ->leftJoin('ta.to_user', 'u')
            ->andWhere('ta.to_user = :user')
            ->andWhere('c.due_date <= ta.end_date ')
            ->andWhere('c.due_date < :endDate')
            ->setParameter('user', $getUser)
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }

    private function getBaseUserQueryBuilder(UserInterface $user)
    {
        return $this->createQueryBuilder('c')
            ->addSelect('p')
            ->leftJoin('c.plant', 'p')
            ->andWhere('p.user = :user')
            ->setParameter('user', $user)
            ->orderBy('c.due_date', 'ASC')
            ->setMaxResults(500 );
    }
}
