<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Filter;

use Letkode\QueryFilterBundle\Filter\FilterCriteria;
use Letkode\QueryFilterBundle\Filter\QueryFilterRequest;
use PHPUnit\Framework\TestCase;

final class QueryFilterRequestTest extends TestCase
{
    public function testFromArrayWithEmptyInput(): void
    {
        self::assertSame([], QueryFilterRequest::fromArray([]));
    }

    public function testFromArrayParsesFiltersWithSingleValue(): void
    {
        $filters = QueryFilterRequest::fromArray([
            'firstName' => [['op' => 'is', 'value' => ['PRUEBA']]],
        ]);

        self::assertCount(1, $filters);
        self::assertInstanceOf(FilterCriteria::class, $filters[0]);
        self::assertSame('firstName', $filters[0]->field);
        self::assertSame('is', $filters[0]->operator);
        self::assertSame(['PRUEBA'], $filters[0]->values);
    }

    public function testFromArrayParsesValuelessOperator(): void
    {
        $filters = QueryFilterRequest::fromArray([
            'email' => [['op' => 'empty']],
        ]);

        self::assertCount(1, $filters);
        self::assertSame('empty', $filters[0]->operator);
        self::assertSame([], $filters[0]->values);
    }

    public function testFromArrayIgnoresFiltersWithoutOp(): void
    {
        $filters = QueryFilterRequest::fromArray([
            'firstName' => [['value' => ['foo']]],
        ]);

        self::assertSame([], $filters);
    }

    public function testFromArrayParsesMultipleConditionsPerField(): void
    {
        $filters = QueryFilterRequest::fromArray([
            'firstName' => [
                ['op' => 'contains', 'value' => ['An']],
                ['op' => 'ends_with', 'value' => ['o']],
            ],
        ]);

        self::assertCount(2, $filters);
        self::assertSame('contains', $filters[0]->operator);
        self::assertSame('ends_with', $filters[1]->operator);
    }

    public function testFromArrayIgnoresNonArrayEntries(): void
    {
        $filters = QueryFilterRequest::fromArray([
            'firstName' => 'not-an-array',
        ]);

        self::assertSame([], $filters);
    }
}
