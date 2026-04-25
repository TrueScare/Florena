<?php

namespace App\Repository;

use App\Entity\PropagationActions;
use App\Entity\User;
use App\Enum\Status;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<PropagationActions>
 */
class PropagationActionsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PropagationActions::class);
    }

    public function findAllWithoutNotification(?User $user = null): array
    {
        $now = new \DateTimeImmutable();

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.plant', 'pl')
            ->leftJoin('pl.user', 'u')
            ->leftJoin('p.notifications', 'n', 'WITH', 'n.is_read = 0')
            ->addSelect('n', 'u', 'pl')
            ->andWhere('n is null')
            ->andWhere('p.planned_date <= :now')
            ->andWhere('p.status IN (:activeStatuses)')
            ->setParameter('now', $now)
            ->setParameter('activeStatuses', [Status::planned, Status::in_progress]);

        if ($user !== null) {
            $queryBuilder
                ->andWhere('u = :user')
                ->setParameter('user', $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }
}
