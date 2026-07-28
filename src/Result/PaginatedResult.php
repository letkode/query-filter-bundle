<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Result;

final readonly class PaginatedResult
{
    public int $totalPages;

    public function __construct(
        public array $data,
        public int $total,
        public int $page,
        public int $perPage,
    ) {
        $this->totalPages = $perPage > 0 ? (int) ceil($total / $perPage) : 0;
    }
}
