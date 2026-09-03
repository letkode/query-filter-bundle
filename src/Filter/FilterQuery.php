<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionReason;

final readonly class FilterQuery
{
    /**
     * @param array<string, mixed> $rawFilters
     */
    public static function fromArray(array $rawFilters): ParsedFilterQuery
    {
        $criteria = [];
        $rejected = [];

        foreach ($rawFilters as $field => $data) {
            $parameter = 'filters.' . $field;

            if (!\is_array($data)) {
                $rejected[] = new QueryParameterRejection(
                    $parameter,
                    RejectionReason::MalformedFilter,
                    \is_scalar($data) ? (string) $data : null,
                );

                continue;
            }

            foreach ($data as $entry) {
                if (!\is_array($entry)) {
                    $rejected[] = new QueryParameterRejection(
                        $parameter,
                        RejectionReason::MalformedFilter,
                        \is_scalar($entry) ? (string) $entry : null,
                    );

                    continue;
                }

                if (!isset($entry['op']) || !\is_string($entry['op'])) {
                    $rejected[] = new QueryParameterRejection($parameter, RejectionReason::MalformedFilter);

                    continue;
                }

                $values = isset($entry['value']) && \is_array($entry['value'])
                    ? array_values(array_map(static fn (mixed $v): string => \is_scalar($v) ? (string) $v : '', $entry['value']))
                    : [];

                $criteria[] = new FilterCriteria((string) $field, $entry['op'], $values);
            }
        }

        return new ParsedFilterQuery($criteria, $rejected);
    }
}
