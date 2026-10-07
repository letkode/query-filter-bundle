<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

/**
 * Holds the globally configured property case for FilterInput, which is an
 * immutable value object with no way to receive it through injection.
 * Set once by the bundle on boot.
 */
final class PropertyCaseRegistry
{
    private static PropertyCase $case = PropertyCase::None;

    public static function set(PropertyCase $case): void
    {
        self::$case = $case;
    }

    public static function get(): PropertyCase
    {
        return self::$case;
    }
}
