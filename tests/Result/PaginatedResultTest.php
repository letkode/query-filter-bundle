<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Result;

use Letkode\QueryFilterBundle\Result\PaginatedResult;
use PHPUnit\Framework\TestCase;

final class PaginatedResultTest extends TestCase
{
    public function testComputesTotalPages(): void
    {
        $result = new PaginatedResult(['a', 'b', 'c'], 50, 1, 20);

        self::assertSame(3, $result->totalPages);
    }

    public function testTotalPagesRoundsUp(): void
    {
        $result = new PaginatedResult([], 21, 1, 20);

        self::assertSame(2, $result->totalPages);
    }

    public function testTotalPagesIsOneWhenItemsFitExactly(): void
    {
        $result = new PaginatedResult([], 20, 1, 20);

        self::assertSame(1, $result->totalPages);
    }

    public function testTotalPagesIsZeroWhenPerPageIsZero(): void
    {
        $result = new PaginatedResult([], 100, 1, 0);

        self::assertSame(0, $result->totalPages);
    }

    public function testTotalPagesIsZeroWhenNoItems(): void
    {
        $result = new PaginatedResult([], 0, 1, 20);

        self::assertSame(0, $result->totalPages);
    }

    public function testExposesAllProperties(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $result = new PaginatedResult($data, 100, 3, 25);

        self::assertSame($data, $result->data);
        self::assertSame(100, $result->total);
        self::assertSame(3, $result->page);
        self::assertSame(25, $result->perPage);
        self::assertSame(4, $result->totalPages);
    }
}
