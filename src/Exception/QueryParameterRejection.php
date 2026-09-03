<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Exception;

/**
 * A single rejected query parameter: which parameter, why, and the offending value.
 *
 * `$parameter` uses dot notation mirroring Symfony property paths
 * (`sort`, `filters.etapa`) so an HTTP layer can key validation errors by it.
 */
final readonly class QueryParameterRejection
{
    public function __construct(
        public string $parameter,
        public RejectionReason $reason,
        public string|null $value = null,
    ) {
    }
}
