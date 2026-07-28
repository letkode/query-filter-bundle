<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Request;

use Letkode\QueryFilterBundle\Filter\QueryFilterRequest;

final class QueryRequestFactory
{
    public static function build(QueryStringRequest $query): QueryRequest
    {
        return new QueryRequest(
            page: $query->page,
            perPage: $query->perPage,
            q: $query->q,
            sort: $query->sort,
            dir: $query->dir,
            filters: QueryFilterRequest::fromArray($query->filters),
        );
    }
}
