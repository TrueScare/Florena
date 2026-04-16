<?php

namespace App\Repository;

use App\Entity\Plants;
use App\Enum\PaginationOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;
use function Symfony\Component\String\b;

/**
 * @extends ServiceEntityRepository<Plants>
 */
class PlantsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Plants::class);
    }

    public function findAllByUser(UserInterface $user)
    {
        return $this->getQueryBuilderFindAllByUser($user)
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilderFindAllByUser(UserInterface $user, PaginationOrder $order = PaginationOrder::NAME_ASC)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->where('p.user = :user')
            ->leftJoin('p.location', 'l')
            ->setParameter('user', $user);

        if ($order) {
            $queryBuilder = $this->handleOrder($queryBuilder, $order);
        }

        return $queryBuilder;
    }

    private function handleOrder(QueryBuilder $queryBuilder, PaginationOrder $order)
    {
        switch ($order) {
            case PaginationOrder::NAME_ASC:
                $queryBuilder->orderBy('p.name', 'ASC');
                break;
            case PaginationOrder::NAME_DESC:
                $queryBuilder->orderBy('p.name', 'DESC');
                break;
            case PaginationOrder::LOCATION_ASC:
                $queryBuilder->orderBy('l.name', 'ASC');
                break;
            case PaginationOrder::LOCATION_DESC:
                $queryBuilder->orderBy('l.name', 'DESC');
                break;
            default:
                break;
        }

        return $queryBuilder;
    }
}
