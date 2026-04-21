<?php

namespace App\Repository;

use App\Entity\Plants;
use App\Enum\PaginationOrder;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\Security\Core\User\UserInterface;

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

    public function findHistoryByUser(UserInterface $user): array
    {
        return $this->getQueryBuilderFindAllByUser($user)
            ->leftJoin('p.care_history', 'h')
            ->addSelect('h')
            ->andWhere('h IS NOT NULL')
            ->getQuery()
            ->getResult();
    }

    public function getQueryBuilderFindAllByUser(UserInterface $user, PaginationOrder $order = PaginationOrder::NAME_ASC, ?string $searchTerm = null): QueryBuilder
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->addSelect('l')
            ->where('p.user = :user')
            ->leftJoin('p.location', 'l')
            ->setParameter('user', $user);

        if ($order) {
            $queryBuilder = $this->handleOrder($queryBuilder, $order);
        }

        if ($searchTerm) {
            $this->handleSearchTerm($queryBuilder, $searchTerm);
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

    private function handleSearchTerm(QueryBuilder $queryBuilder, ?string $searchTerm): QueryBuilder
    {
        $queryBuilder->andWhere('p.name like :searchTerm or l.name like :searchTerm')
            ->setParameter('searchTerm', '%' . $searchTerm . '%');
        return $queryBuilder;
    }
}
