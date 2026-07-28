<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Request;

use Symfony\Component\Validator\Constraints as Assert;

final readonly class QueryStringRequest
{
    public function __construct(
        #[Assert\Positive]
        public int $page = 1,
        #[Assert\Range(min: 1, max: 100)]
        public int $perPage = 20,
        public string|null $q = null,
        public string|null $sort = null,
        #[Assert\Choice(choices: ['asc', 'desc'])]
        public string $dir = 'asc',
        public array $filters = [],
    ) {
    }
}
