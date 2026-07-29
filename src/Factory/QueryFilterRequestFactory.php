<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Factory;

use Letkode\QueryFilterBundle\Filter\QueryFilter;
use Letkode\QueryFilterBundle\Request\QueryFilterRequest;
use Letkode\QueryFilterBundle\Request\QueryFilterStringRequest;

final class QueryFilterRequestFactory
{
    public static function build(QueryFilterStringRequest $query): QueryFilterRequest
    {
        return new QueryFilterRequest(
            page: $query->page,
            perPage: $query->perPage,
            q: $query->q,
            sort: $query->sort,
            dir: $query->dir,
            filters: QueryFilter::fromArray($query->filters),
        );
    }
}
