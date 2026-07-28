<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

final readonly class FilterInput
{
    private function __construct(
        public string $type,
        public string|null $path = null,
        private string|null $cast = null,
    ) {
    }

    public static function text(string|null $path = null): self
    {
        return new self('text', $path);
    }

    public static function bool(string|null $path = null): self
    {
        return new self('scalar', $path, 'bool');
    }

    public static function int(string|null $path = null): self
    {
        return new self('scalar', $path, 'int');
    }

    public static function float(string|null $path = null): self
    {
        return new self('scalar', $path, 'float');
    }

    public static function array(string|null $path = null): self
    {
        return new self('array', $path);
    }

    public static function number(string|null $path = null): self
    {
        return new self('number', $path, 'float');
    }

    public static function date(string|null $path = null): self
    {
        return new self('date', $path, 'date');
    }

    public function castValue(string $value): mixed
    {
        return match ($this->cast) {
            'bool' => 'true' === strtolower($value),
            'int' => (int) $value,
            'float' => (float) $value,
            'date' => new \DateTimeImmutable($value),
            default => $value,
        };
    }

    /** @param list<string> $values */
    public function castValues(array $values): mixed
    {
        return array_map($this->castValue(...), $values);
    }
}
