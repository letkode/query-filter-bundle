<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

final readonly class FilterQuery
{
    /**
     * @param array<string, mixed> $rawFilters
     *
     * @return list<FilterCriteria>
     */
    public static function fromArray(array $rawFilters): array
    {
        $filters = [];

        foreach ($rawFilters as $field => $data) {
            if (!\is_array($data)) {
                continue;
            }

            foreach ($data as $entry) {
                if (!\is_array($entry) || !isset($entry['op'])) {
                    continue;
                }

                $values = isset($entry['value']) && \is_array($entry['value'])
                    ? array_values(array_map('strval', $entry['value']))
                    : [];

                $filters[] = new FilterCriteria((string) $field, (string) $entry['op'], $values);
            }
        }

        return $filters;
    }
}
