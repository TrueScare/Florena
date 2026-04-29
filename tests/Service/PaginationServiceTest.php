<?php

namespace App\Tests\Service;

use App\Enum\PaginationLimit;
use App\Enum\PaginationOrder;
use App\Service\Pagination\PaginationService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;

final class PaginationServiceTest extends TestCase
{
    private PaginationService $service;

    protected function setUp(): void
    {
        $this->service = new PaginationService();
    }

    public function testDefaultsWhenNoQueryParams(): void
    {
        $request = Request::create('/plants');
        $info = $this->service->getPageInfoFromRequest($request);

        self::assertSame(PaginationOrder::NAME_ASC, $info->getOrder());
        self::assertSame(PaginationLimit::ten, $info->getLimit());
        self::assertSame(1, $info->getPage());
        self::assertNull($info->getSearchTerm());
    }

    public function testParsesQueryParamsCorrectly(): void
    {
        $request = Request::create('/plants', 'GET', [
            'order'      => PaginationOrder::NAME_DESC->value,
            'limit'      => (string) PaginationLimit::twentyfive->value,
            'page'       => '3',
            'searchTerm' => 'Monstera',
        ]);
        $info = $this->service->getPageInfoFromRequest($request);

        self::assertSame(PaginationOrder::NAME_DESC, $info->getOrder());
        self::assertSame(PaginationLimit::twentyfive, $info->getLimit());
        self::assertSame(3, $info->getPage());
        self::assertSame('Monstera', $info->getSearchTerm());
    }

    public function testFallsBackToDefaultForInvalidEnumValues(): void
    {
        $request = Request::create('/plants', 'GET', [
            'order' => 'invalidOrder',
            'limit' => '999',
        ]);
        $info = $this->service->getPageInfoFromRequest($request);

        // Invalid values fall back to the default via the ?? operator — no exception thrown.
        self::assertSame(PaginationOrder::NAME_ASC, $info->getOrder());
        self::assertSame(PaginationLimit::ten, $info->getLimit());

    }
}
