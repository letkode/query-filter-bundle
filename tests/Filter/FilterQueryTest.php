<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Filter;

use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Filter\FilterCriteria;
use Letkode\QueryFilterBundle\Filter\FilterQuery;
use PHPUnit\Framework\TestCase;

final class FilterQueryTest extends TestCase
{
    public function testFromArrayWithEmptyInput(): void
    {
        $parsed = FilterQuery::fromArray([]);

        self::assertSame([], $parsed->criteria);
        self::assertSame([], $parsed->rejected);
    }

    public function testFromArrayParsesFiltersWithSingleValue(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => [['op' => 'is', 'value' => ['PRUEBA']]],
        ]);

        self::assertCount(1, $parsed->criteria);
        self::assertInstanceOf(FilterCriteria::class, $parsed->criteria[0]);
        self::assertSame('firstName', $parsed->criteria[0]->field);
        self::assertSame('is', $parsed->criteria[0]->operator);
        self::assertSame(['PRUEBA'], $parsed->criteria[0]->values);
        self::assertSame([], $parsed->rejected);
    }

    public function testFromArrayParsesValuelessOperator(): void
    {
        $parsed = FilterQuery::fromArray([
            'email' => [['op' => 'empty']],
        ]);

        self::assertCount(1, $parsed->criteria);
        self::assertSame('empty', $parsed->criteria[0]->operator);
        self::assertSame([], $parsed->criteria[0]->values);
    }

    public function testFromArrayParsesMultipleConditionsPerField(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => [
                ['op' => 'contains', 'value' => ['An']],
                ['op' => 'ends_with', 'value' => ['o']],
            ],
        ]);

        self::assertCount(2, $parsed->criteria);
        self::assertSame('contains', $parsed->criteria[0]->operator);
        self::assertSame('ends_with', $parsed->criteria[1]->operator);
    }

    public function testFromArrayRejectsFilterWhoseValueIsNotAnArray(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => 'not-an-array',
        ]);

        self::assertSame([], $parsed->criteria);
        self::assertCount(1, $parsed->rejected);
        self::assertSame('filters.firstName', $parsed->rejected[0]->parameter);
        self::assertSame(RejectionReason::MalformedFilter, $parsed->rejected[0]->reason);
        self::assertSame('not-an-array', $parsed->rejected[0]->value);
    }

    public function testFromArrayRejectsFilterEntryThatIsNotAnArray(): void
    {
        $parsed = FilterQuery::fromArray([
            'stage' => ['is:done'],
        ]);

        self::assertSame([], $parsed->criteria);
        self::assertCount(1, $parsed->rejected);
        self::assertSame('filters.stage', $parsed->rejected[0]->parameter);
        self::assertSame(RejectionReason::MalformedFilter, $parsed->rejected[0]->reason);
        self::assertSame('is:done', $parsed->rejected[0]->value);
    }

    public function testFromArrayRejectsFilterEntryWithoutOp(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => [['value' => ['foo']]],
        ]);

        self::assertSame([], $parsed->criteria);
        self::assertCount(1, $parsed->rejected);
        self::assertSame('filters.firstName', $parsed->rejected[0]->parameter);
        self::assertSame(RejectionReason::MalformedFilter, $parsed->rejected[0]->reason);
        self::assertNull($parsed->rejected[0]->value);
    }

    public function testFromArrayRejectsFilterEntryWhoseOpIsNotAString(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => [['op' => 123, 'value' => ['foo']]],
        ]);

        self::assertSame([], $parsed->criteria);
        self::assertCount(1, $parsed->rejected);
        self::assertSame(RejectionReason::MalformedFilter, $parsed->rejected[0]->reason);
    }

    public function testFromArrayKeepsWellFormedEntriesAlongsideRejections(): void
    {
        $parsed = FilterQuery::fromArray([
            'firstName' => [['op' => 'is', 'value' => ['Ana']]],
            'stage' => 'is:done',
        ]);

        self::assertCount(1, $parsed->criteria);
        self::assertSame('firstName', $parsed->criteria[0]->field);
        self::assertCount(1, $parsed->rejected);
        self::assertSame('filters.stage', $parsed->rejected[0]->parameter);
    }
}
