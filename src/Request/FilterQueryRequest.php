<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Request;

use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Filter\FilterCriteria;
use Letkode\QueryFilterBundle\Filter\FilterQuery;

final readonly class FilterQueryRequest
{
    /**
     * @param list<FilterCriteria>          $filters
     * @param list<QueryParameterRejection> $rejected Malformed filter entries dropped while parsing
     */
    public function __construct(
        public int $page = 1,
        public int $perPage = 20,
        public string|null $q = null,
        public string|null $sort = null,
        public string $dir = 'asc',
        public array $filters = [],
        public array $rejected = [],
    ) {
    }

    /**
     * @param array<string, mixed> $params
     */
    public static function fromArray(array $params): self
    {
        $dirRaw = $params['dir'] ?? 'asc';
        $dir = strtolower(\is_scalar($dirRaw) ? (string) $dirRaw : 'asc');

        $rawFilters = isset($params['filters']) && \is_array($params['filters']) ? $params['filters'] : [];

        $pageRaw = $params['page'] ?? 1;
        $perPageRaw = $params['perPage'] ?? $params['per_page'] ?? 20;
        $qRaw = $params['q'] ?? null;
        $sortRaw = $params['sort'] ?? null;

        $parsed = FilterQuery::fromArray($rawFilters);

        return new self(
            page: max(1, \is_scalar($pageRaw) ? (int) $pageRaw : 1),
            perPage: min(100, max(1, \is_scalar($perPageRaw) ? (int) $perPageRaw : 20)),
            q: \is_scalar($qRaw) && '' !== (string) $qRaw ? (string) $qRaw : null,
            sort: \is_scalar($sortRaw) && '' !== (string) $sortRaw ? (string) $sortRaw : null,
            dir: \in_array($dir, ['asc', 'desc'], true) ? $dir : 'asc',
            filters: $parsed->criteria,
            rejected: $parsed->rejected,
        );
    }
}
