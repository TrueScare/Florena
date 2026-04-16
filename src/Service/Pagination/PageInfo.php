<?php

namespace App\Service\Pagination;

use App\Enum\PaginationLimit;
use App\Enum\PaginationOrder;

class PageInfo
{
    private PaginationLimit $limit;
    private int $page;
    private PaginationOrder $order;

    /**
     * @param PaginationOrder $order
     * @param PaginationLimit $limit
     * @param int $page
     */
    public function __construct(PaginationOrder $order = PaginationOrder::NAME_ASC, PaginationLimit $limit = PaginationLimit::ten, int $page = 1)
    {
        $this->limit = $limit;
        $this->page = $page;
        $this->order = $order;
    }

    public function getLimit(): PaginationLimit
    {
        return $this->limit;
    }

    public function setLimit(PaginationLimit $limit): void
    {
        $this->limit = $limit;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getOrder(): PaginationOrder
    {
        return $this->order;
    }

    public function setOrder(PaginationOrder $order): void
    {
        $this->order = $order;
    }

}
