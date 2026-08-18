<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Result;

use Letkode\QueryFilterBundle\Result\PaginatedResultRepository;
use PHPUnit\Framework\TestCase;

final class PaginatedResultRepositoryTest extends TestCase
{
    public function testComputesTotalPages(): void
    {
        $result = new PaginatedResultRepository(['a', 'b', 'c'], 50, 1, 20);

        self::assertSame(3, $result->totalPages);
    }

    public function testTotalPagesRoundsUp(): void
    {
        $result = new PaginatedResultRepository([], 21, 1, 20);

        self::assertSame(2, $result->totalPages);
    }

    public function testTotalPagesIsOneWhenItemsFitExactly(): void
    {
        $result = new PaginatedResultRepository([], 20, 1, 20);

        self::assertSame(1, $result->totalPages);
    }

    public function testTotalPagesIsZeroWhenPerPageIsZero(): void
    {
        $result = new PaginatedResultRepository([], 100, 1, 0);

        self::assertSame(0, $result->totalPages);
    }

    public function testTotalPagesIsZeroWhenNoItems(): void
    {
        $result = new PaginatedResultRepository([], 0, 1, 20);

        self::assertSame(0, $result->totalPages);
    }

    public function testExposesAllProperties(): void
    {
        $data = [['id' => 1], ['id' => 2]];
        $result = new PaginatedResultRepository($data, 100, 3, 25);

        self::assertSame($data, $result->data);
        self::assertSame(100, $result->total);
        self::assertSame(3, $result->page);
        self::assertSame(25, $result->perPage);
        self::assertSame(4, $result->totalPages);
    }
}
