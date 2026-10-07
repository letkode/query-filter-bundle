<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

final readonly class FilterInput
{
    private function __construct(
        public FilterCastType $type,
        public string|null $alias = null,
        public string|null $property = null,
        public PropertyCase|null $propertyCase = null,
        public string|null $expression = null,
    ) {
        if (null !== $expression && (null !== $alias || null !== $property || null !== $propertyCase)) {
            throw new \InvalidArgumentException('A FilterInput expression cannot be combined with an alias, a property or a property case.');
        }
    }

    public static function text(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Text, $alias, $property, $propertyCase, $expression);
    }

    public static function bool(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Bool, $alias, $property, $propertyCase, $expression);
    }

    public static function int(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Int, $alias, $property, $propertyCase, $expression);
    }

    public static function float(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Float, $alias, $property, $propertyCase, $expression);
    }

    public static function array(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::ArrayType, $alias, $property, $propertyCase, $expression);
    }

    public static function number(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Number, $alias, $property, $propertyCase, $expression);
    }

    public static function date(string|null $alias = null, string|null $property = null, PropertyCase|null $propertyCase = null, string|null $expression = null): self
    {
        return new self(FilterCastType::Date, $alias, $property, $propertyCase, $expression);
    }

    /**
     * The property a filter targets: the explicit property when given, otherwise
     * the key converted to this input's property case, falling back to the
     * configured one. An explicit property is never converted. Qualifying it
     * with `$alias` (or a default alias of the consumer's own) is up to the consumer.
     */
    public function resolveProperty(string $key): string
    {
        return $this->property ?? ($this->propertyCase ?? PropertyCaseRegistry::get())->convert($key);
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
