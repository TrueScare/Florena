<?php

namespace App\Repository;

use App\Entity\PlantNotes;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<PlantNotes>
 */
class PlantNotesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, PlantNotes::class);
    }

    public function findAllByUser(UserInterface $user): array
    {
        return $this->createQueryBuilder('n')
            ->addSelect('p')
            ->leftJoin('n.plant', 'p')
            ->andWhere('n.user = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();
    }
}
