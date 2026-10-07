<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Filter;

use Symfony\Component\String\UnicodeString;

/**
 * How the fields a filter targets are spelled (e.g. the properties of an entity),
 * used to translate the key a client sends (`legal_name`, `legalName`, `legal-name`)
 * into that spelling.
 *
 * The converters accept any input spelling, so only the target needs declaring.
 */
enum PropertyCase: string
{
    case None = 'none';
    case Camel = 'camel';
    case Snake = 'snake';

    public function convert(string $field): string
    {
        return match ($this) {
            self::None => $field,
            self::Camel => new UnicodeString($field)->camel()->toString(),
            self::Snake => new UnicodeString($field)->snake()->toString(),
        };
    }
}
