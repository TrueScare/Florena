<?php

namespace App\Service\Pagination;

class PaginationResult
{
    private array $items;
    private int $total;
    private int $page;
    private int $limit;
    private string $order;
    private ?string $searchTerm;

    /**
     * @param array $items
     * @param int $totalResults
     * @param int $page
     * @param int $limit
     * @param string $order
     * @param string|null $searchTerm
     */
    public function __construct(array $items, int $totalResults, int $page, int $limit, string $order, ?string $searchTerm)
    {
        $this->items = $items;
        $this->total = $totalResults;
        $this->page = $page;
        $this->limit = $limit;
        $this->order = $order;
        $this->searchTerm = $searchTerm;
    }

    public function getItems(): array
    {
        return $this->items;
    }

    public function setItems(array $items): void
    {
        $this->items = $items;
    }

    public function getTotal(): int
    {
        return $this->total;
    }

    public function setTotal(int $total): void
    {
        $this->total = $total;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): void
    {
        $this->page = $page;
    }

    public function getLimit(): int
    {
        return $this->limit;
    }

    public function setLimit(int $limit): void
    {
        $this->limit = $limit;
    }

    public function getPages(): float
    {
        return ceil($this->total / $this->limit);
    }

    public function getOrder(): string
    {
        return $this->order;
    }

    public function setOrder(string $order): void
    {
        $this->order = $order;
    }

    public function getSearchTerm(): ?string
    {
        return $this->searchTerm;
    }

    public function setSearchTerm(?string $searchTerm): void
    {
        $this->searchTerm = $searchTerm;
    }
}
