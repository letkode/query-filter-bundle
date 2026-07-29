<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Factory;

use Letkode\QueryFilterBundle\Filter\FilterQuery;
use Letkode\QueryFilterBundle\Request\FilterQueryRequest;
use Letkode\QueryFilterBundle\Request\FilterQueryStringRequest;

final class FilterQueryRequestFactory
{
    public static function build(FilterQueryStringRequest $query): FilterQueryRequest
    {
        return new FilterQueryRequest(
            page: $query->page,
            perPage: $query->perPage,
            q: $query->q,
            sort: $query->sort,
            dir: $query->dir,
            filters: FilterQuery::fromArray($query->filters),
        );
    }
}
