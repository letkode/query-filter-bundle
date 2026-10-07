<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

final readonly class FilterInput
{
    private function __construct(
        public FilterCastType $type,
        public string|null $path = null,
        public PropertyCase|null $propertyCase = null,
    ) {
    }

    public static function text(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Text, $path, $propertyCase);
    }

    public static function bool(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Bool, $path, $propertyCase);
    }

    public static function int(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Int, $path, $propertyCase);
    }

    public static function float(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Float, $path, $propertyCase);
    }

    public static function array(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::ArrayType, $path, $propertyCase);
    }

    public static function number(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Number, $path, $propertyCase);
    }

    public static function date(string|null $path = null, PropertyCase|null $propertyCase = null): self
    {
        return new self(FilterCastType::Date, $path, $propertyCase);
    }

    /**
     * The path a filter targets: the explicit path when given, otherwise the key
     * converted to this input's property case, falling back to the configured one.
     * An explicit path is never converted.
     */
    public function resolvePath(string $key): string
    {
        return $this->path ?? ($this->propertyCase ?? PropertyCaseRegistry::get())->convert($key);
    }

    public function castValue(string $value): mixed
    {
        return $this->type->cast($value);
    }

    /**
     * @param list<string> $values
     *
     * @return list<mixed>
     */
    public function castValues(array $values): array
    {
        return array_map($this->castValue(...), $values);
    }
}
