<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;

/**
 * Result of parsing raw filter input: the well-formed criteria plus the
 * rejections for entries that could not be parsed (malformed structure).
 */
final readonly class ParsedFilterQuery
{
    /**
     * @param list<FilterCriteria>          $criteria
     * @param list<QueryParameterRejection> $rejected
     */
    public function __construct(
        public array $criteria = [],
        public array $rejected = [],
    ) {
    }
}
