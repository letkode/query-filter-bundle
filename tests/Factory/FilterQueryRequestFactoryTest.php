<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Factory;

use Letkode\QueryFilterBundle\Factory\FilterQueryRequestFactory;
use Letkode\QueryFilterBundle\Filter\FilterCriteria;
use Letkode\QueryFilterBundle\Request\FilterQueryStringRequest;
use PHPUnit\Framework\TestCase;

final class FilterQueryRequestFactoryTest extends TestCase
{
    public function testBuildCopiesScalarsFromQueryString(): void
    {
        $query = new FilterQueryStringRequest(page: 2, perPage: 50, q: 'term', sort: 'name', dir: 'desc');

        $result = FilterQueryRequestFactory::build($query);

        self::assertSame(2, $result->page);
        self::assertSame(50, $result->perPage);
        self::assertSame('term', $result->q);
        self::assertSame('name', $result->sort);
        self::assertSame('desc', $result->dir);
    }

    public function testBuildParsesRawFiltersIntoFilterCriteria(): void
    {
        $query = new FilterQueryStringRequest(filters: [
            'firstName' => [['op' => 'is', 'value' => ['Ana']]],
        ]);

        $result = FilterQueryRequestFactory::build($query);

        self::assertCount(1, $result->filters);
        self::assertInstanceOf(FilterCriteria::class, $result->filters[0]);
        self::assertSame('firstName', $result->filters[0]->field);
        self::assertSame('is', $result->filters[0]->operator);
        self::assertSame(['Ana'], $result->filters[0]->values);
    }

    public function testBuildWithDefaultsProducesEmptyFilters(): void
    {
        $result = FilterQueryRequestFactory::build(new FilterQueryStringRequest());

        self::assertSame([], $result->filters);
    }
}
