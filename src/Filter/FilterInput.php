<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

final readonly class FilterInput
{
    private function __construct(
        public FilterCastType $type,
        public string|null $path = null,
    ) {
    }

    public static function text(string|null $path = null): self
    {
        return new self(FilterCastType::Text, $path);
    }

    public static function bool(string|null $path = null): self
    {
        return new self(FilterCastType::Bool, $path);
    }

    public static function int(string|null $path = null): self
    {
        return new self(FilterCastType::Int, $path);
    }

    public static function float(string|null $path = null): self
    {
        return new self(FilterCastType::Float, $path);
    }

    public static function array(string|null $path = null): self
    {
        return new self(FilterCastType::ArrayType, $path);
    }

    public static function number(string|null $path = null): self
    {
        return new self(FilterCastType::Number, $path);
    }

    public static function date(string|null $path = null): self
    {
        return new self(FilterCastType::Date, $path);
    }

    public function castValue(string $value): mixed
    {
        return $this->type->cast($value);
    }

    /** @param list<string> $values */
    public function castValues(array $values): mixed
    {
        return array_map($this->castValue(...), $values);
    }
}
