<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Filter;

use Letkode\QueryFilterBundle\Filter\FilterCastType;
use Letkode\QueryFilterBundle\Filter\FilterInput;
use Letkode\QueryFilterBundle\Filter\PropertyCase;
use Letkode\QueryFilterBundle\Filter\PropertyCaseRegistry;
use PHPUnit\Framework\TestCase;

final class FilterInputTest extends TestCase
{
    protected function tearDown(): void
    {
        PropertyCaseRegistry::set(PropertyCase::None);
    }

    public function testResolvePathKeepsTheKeyByDefault(): void
    {
        self::assertSame('legal_name', FilterInput::text()->resolvePath('legal_name'));
    }

    public function testResolvePathUsesTheGlobalPropertyCase(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legalName', FilterInput::text()->resolvePath('legal_name'));
    }

    public function testResolvePathNeverConvertsAnExplicitPath(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('co.legal_name', FilterInput::text(path: 'co.legal_name')->resolvePath('company_name'));
        self::assertSame(
            'co.legal_name',
            FilterInput::text(path: 'co.legal_name', propertyCase: PropertyCase::Snake)->resolvePath('company_name'),
        );
    }

    public function testLocalPropertyCaseOverridesTheGlobalOne(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legal_name', FilterInput::text(propertyCase: PropertyCase::Snake)->resolvePath('legalName'));
    }

    public function testLocalNoneOverridesAGlobalCase(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legal_name', FilterInput::text(propertyCase: PropertyCase::None)->resolvePath('legal_name'));
    }

    public function testEveryFactoryAcceptsALocalPropertyCase(): void
    {
        foreach (['text', 'bool', 'int', 'float', 'array', 'number', 'date'] as $factory) {
            $input = FilterInput::$factory(propertyCase: PropertyCase::Camel);

            self::assertSame(PropertyCase::Camel, $input->propertyCase, $factory);
        }
    }

    public function testTextFactoryCreatesTextType(): void
    {
        $input = FilterInput::text();

        self::assertSame(FilterCastType::Text, $input->type);
        self::assertNull($input->path);
    }

    public function testTextFactoryAcceptsCustomPath(): void
    {
        $input = FilterInput::text('u.name');

        self::assertSame('u.name', $input->path);
    }

    public function testNumberFactoryCreatesNumberType(): void
    {
        $input = FilterInput::number();

        self::assertSame(FilterCastType::Number, $input->type);
        self::assertNull($input->path);
    }

    public function testNumberCastsToFloat(): void
    {
        $input = FilterInput::number();

        self::assertSame(42.5, $input->castValue('42.5'));
        self::assertSame(10.0, $input->castValue('10'));
    }

    public function testDateFactoryCreatesDateType(): void
    {
        $input = FilterInput::date();

        self::assertSame(FilterCastType::Date, $input->type);
        self::assertNull($input->path);
    }

    public function testDateCastsToDateTimeImmutable(): void
    {
        $input = FilterInput::date();
        $result = $input->castValue('2024-01-15');

        self::assertInstanceOf(\DateTimeImmutable::class, $result);
        self::assertSame('2024-01-15', $result->format('Y-m-d'));
    }

    public function testDateCastsFullDatetime(): void
    {
        $input = FilterInput::date();
        $result = $input->castValue('2024-06-30T12:00:00');

        self::assertInstanceOf(\DateTimeImmutable::class, $result);
        self::assertSame('2024-06-30', $result->format('Y-m-d'));
    }

    public function testDateCastValuesReturnsMappedDatetimes(): void
    {
        $input = FilterInput::date();
        $result = $input->castValues(['2024-01-01', '2024-12-31']);

        self::assertCount(2, $result);
        self::assertInstanceOf(\DateTimeImmutable::class, $result[0]);
        self::assertInstanceOf(\DateTimeImmutable::class, $result[1]);
        self::assertSame('2024-01-01', $result[0]->format('Y-m-d'));
        self::assertSame('2024-12-31', $result[1]->format('Y-m-d'));
    }

    public function testNumberFactoryAcceptsCustomPath(): void
    {
        $input = FilterInput::number('p.price');

        self::assertSame('p.price', $input->path);
    }

    public function testDateFactoryAcceptsCustomPath(): void
    {
        $input = FilterInput::date('u.createdAt');

        self::assertSame('u.createdAt', $input->path);
    }
}
