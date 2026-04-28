<?php

namespace App\Repository;

use App\Entity\CareTask;
use App\Entity\TaskAssignments;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<TaskAssignments>
 */
class TaskAssignmentsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, TaskAssignments::class);
    }

    public function countTaskAssignmentsForCareTaskByUser(CareTask $careTask, UserInterface $user): int{
        return $this->createQueryBuilder('ta')
            ->select('COUNT(ta.id)')
            ->join('ta.care_task', 'ct')
            ->join('ta.to_user', 'u')
            ->where('ct.id = :taskId')
            ->andWhere('u.id = :userId')
            ->setParameter('taskId', $careTask->getId())
            ->setParameter('userId', $user->getId())
            ->getQuery()
            ->getSingleScalarResult();
    }

    //    /**
    //     * @return TaskAssignments[] Returns an array of TaskAssignments objects
    //     */
    //    public function findByExampleField($value): array
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->orderBy('t.id', 'ASC')
    //            ->setMaxResults(10)
    //            ->getQuery()
    //            ->getResult()
    //        ;
    //    }

    //    public function findOneBySomeField($value): ?TaskAssignments
    //    {
    //        return $this->createQueryBuilder('t')
    //            ->andWhere('t.exampleField = :val')
    //            ->setParameter('val', $value)
    //            ->getQuery()
    //            ->getOneOrNullResult()
    //        ;
    //    }
}
