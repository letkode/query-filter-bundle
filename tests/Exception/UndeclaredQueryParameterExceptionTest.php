<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Exception;

use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Exception\UndeclaredQueryParameterException;
use PHPUnit\Framework\TestCase;

final class UndeclaredQueryParameterExceptionTest extends TestCase
{
    public function testCarriesTheRejectionList(): void
    {
        $rejections = [
            new QueryParameterRejection('sort', RejectionReason::NotSortable, 'invented'),
            new QueryParameterRejection('filters.stage', RejectionReason::NotFilterable, 'stage'),
        ];

        $exception = new UndeclaredQueryParameterException($rejections);

        self::assertSame($rejections, $exception->rejections);
        self::assertSame('The query contains undeclared or invalid parameters.', $exception->getMessage());
    }

    public function testRejectsAnEmptyList(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /* @phpstan-ignore argument.type (deliberately violating the non-empty contract) */
        new UndeclaredQueryParameterException([]);
    }
}
