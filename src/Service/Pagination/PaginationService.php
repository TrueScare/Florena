<?php

namespace App\Service\Pagination;

use App\Enum\PaginationLimit;
use App\Enum\PaginationOrder;
use BackedEnum;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Symfony\Component\HttpFoundation\Request;

class PaginationService
{
    protected int $maxLimit = 100;

    public function paginate(QueryBuilder $queryBuilder, int $page = 1, PaginationLimit $limit = PaginationLimit::ten, PaginationOrder $order = PaginationOrder::NAME_ASC): PaginationResult
    {
        $offset = ($page - 1) * $limit->value;

        // add pagination limits to query
        $queryBuilder->setMaxResults($limit->value)->setFirstResult($offset);

        $paginator = new Paginator($queryBuilder);
        $totalResults = $paginator->count();

        return new PaginationResult(iterator_to_array($paginator), $totalResults, $page, $limit->value, $order->value);
    }

    public function getPageInfoFromRequest(Request $request): PageInfo
    {
        return new PageInfo(
            PaginationOrder::tryFrom($request->query->get('order', PaginationOrder::NAME_ASC->value)),
            PaginationLimit::tryFrom($request->query->get('limit', PaginationLimit::ten->value)),
            $request->query->get('page', 1)
        );
    }
}
