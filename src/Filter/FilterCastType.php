<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

enum FilterCastType: string
{
    case Text = 'text';
    case Bool = 'bool';
    case Int = 'int';
    case Float = 'float';
    case Number = 'number';
    case Date = 'date';
    case ArrayType = 'array';

    public function cast(string $value): mixed
    {
        return match ($this) {
            self::Bool => 'true' === strtolower($value),
            self::Int => (int) $value,
            self::Float, self::Number => (float) $value,
            self::Date => new \DateTimeImmutable($value),
            self::Text, self::ArrayType => $value,
        };
    }
}
