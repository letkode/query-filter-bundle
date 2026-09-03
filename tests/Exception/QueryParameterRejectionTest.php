<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Exception;

use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use PHPUnit\Framework\TestCase;

final class QueryParameterRejectionTest extends TestCase
{
    public function testExposesItsData(): void
    {
        $rejection = new QueryParameterRejection('filters.etapa', RejectionReason::UnknownOperator, 'like');

        self::assertSame('filters.etapa', $rejection->parameter);
        self::assertSame(RejectionReason::UnknownOperator, $rejection->reason);
        self::assertSame('like', $rejection->value);
    }

    public function testValueDefaultsToNull(): void
    {
        $rejection = new QueryParameterRejection('sort', RejectionReason::NotSortable);

        self::assertNull($rejection->value);
    }

    public function testReasonValuesAreStableTranslationKeys(): void
    {
        self::assertSame('not_sortable', RejectionReason::NotSortable->value);
        self::assertSame('not_filterable', RejectionReason::NotFilterable->value);
        self::assertSame('unknown_operator', RejectionReason::UnknownOperator->value);
        self::assertSame('malformed_filter', RejectionReason::MalformedFilter->value);
    }
}
