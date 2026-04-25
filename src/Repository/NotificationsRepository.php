<?php

namespace App\Repository;

use App\Entity\Notifications;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Notifications>
 */
class NotificationsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Notifications::class);
    }


    /**
     * @return Notifications[]
     */
    public function findUnreadActiveByUser(User $user): array
    {
        return $this->createQueryBuilder('n')
            ->andWhere('n.user = :user')
            ->andWhere('n.is_read = false')
            ->andWhere('n.is_active = true')
            ->setParameter('user', $user)
            ->orderBy('n.created_at', 'DESC')
            ->getQuery()
            ->getResult();
    }

    public function countUnreadActiveByUser(User $user): int
    {
        return (int) $this->createQueryBuilder('n')
            ->select('COUNT(n.id)')
            ->andWhere('n.user = :user')
            ->andWhere('n.is_read = false')
            ->andWhere('n.is_active = true')
            ->setParameter('user', $user)
            ->getQuery()
            ->getSingleScalarResult();
    }
}
