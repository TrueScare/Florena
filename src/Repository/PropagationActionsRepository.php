<?php

namespace App\Repository;

use App\Entity\PropagationActions;
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

    public function findAllWithoutNotification(): array
    {
        $endDate = new \DateTimeImmutable("tomorrow");

        return $this->createQueryBuilder('p')
            ->leftJoin('p.plant', 'pl')
            ->leftJoin('pl.user', 'u')
            ->leftJoin('p.notifications', 'n', 'WITH', 'n.is_read = 0')
            ->addSelect('n', 'u', 'pl')
            ->andWhere('n is null')
            ->andWhere('p.planned_date < :endDate')
            ->setParameter('endDate', $endDate)
            ->getQuery()
            ->getResult();
    }
}
