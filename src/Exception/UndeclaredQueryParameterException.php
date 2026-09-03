<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Exception;

/**
 * Thrown when a query carries parameters that are not declared by the
 * consumer (unknown sort/filter field, unsupported operator) or are
 * structurally malformed.
 *
 * Carries the full list of rejections so the caller can report them all at
 * once. This class is deliberately HTTP-agnostic: translating it to a
 * response (e.g. 422 with violations) is the caller's responsibility.
 */
final class UndeclaredQueryParameterException extends \RuntimeException
{
    /**
     * @param non-empty-list<QueryParameterRejection> $rejections
     */
    public function __construct(public readonly array $rejections)
    {
        if ([] === $rejections) {
            throw new \InvalidArgumentException('UndeclaredQueryParameterException requires at least one rejection.');
        }

        parent::__construct('The query contains undeclared or invalid parameters.');
    }
}
