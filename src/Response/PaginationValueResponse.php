<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Response;

final readonly class PaginationValueResponse
{
    public function __construct(
        public int $total,
        public int $perPage,
        public int $totalPages,
        public int $page,
    ) {
    }
}
