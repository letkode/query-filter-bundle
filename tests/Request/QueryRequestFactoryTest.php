<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Request;

use Letkode\QueryFilterBundle\Filter\FilterCriteria;
use Letkode\QueryFilterBundle\Request\QueryRequestFactory;
use Letkode\QueryFilterBundle\Request\QueryStringRequest;
use PHPUnit\Framework\TestCase;

final class QueryRequestFactoryTest extends TestCase
{
    public function testBuildCopiesScalarsFromQueryString(): void
    {
        $query = new QueryStringRequest(page: 2, perPage: 50, q: 'term', sort: 'name', dir: 'desc');

        $result = QueryRequestFactory::build($query);

        self::assertSame(2, $result->page);
        self::assertSame(50, $result->perPage);
        self::assertSame('term', $result->q);
        self::assertSame('name', $result->sort);
        self::assertSame('desc', $result->dir);
    }

    public function testBuildParsesRawFiltersIntoFilterCriteria(): void
    {
        $query = new QueryStringRequest(filters: [
            'firstName' => [['op' => 'is', 'value' => ['Ana']]],
        ]);

        $result = QueryRequestFactory::build($query);

        self::assertCount(1, $result->filters);
        self::assertInstanceOf(FilterCriteria::class, $result->filters[0]);
        self::assertSame('firstName', $result->filters[0]->field);
        self::assertSame('is', $result->filters[0]->operator);
        self::assertSame(['Ana'], $result->filters[0]->values);
    }

    public function testBuildWithDefaultsProducesEmptyFilters(): void
    {
        $result = QueryRequestFactory::build(new QueryStringRequest());

        self::assertSame([], $result->filters);
    }
}
