<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Filter;

use Letkode\QueryFilterBundle\Filter\FilterCastType;
use Letkode\QueryFilterBundle\Filter\FilterInput;
use Letkode\QueryFilterBundle\Filter\PropertyCase;
use Letkode\QueryFilterBundle\Filter\PropertyCaseRegistry;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

final class FilterInputTest extends TestCase
{
    protected function tearDown(): void
    {
        PropertyCaseRegistry::set(PropertyCase::None);
    }

    public function testResolvePropertyKeepsTheKeyByDefault(): void
    {
        self::assertSame('legal_name', FilterInput::text()->resolveProperty('legal_name'));
    }

    public function testResolvePropertyUsesTheGlobalPropertyCase(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legalName', FilterInput::text()->resolveProperty('legal_name'));
    }

    public function testExplicitPropertyIsNeverConverted(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legal_name', FilterInput::text(property: 'legal_name')->resolveProperty('company_name'));
        self::assertSame(
            'legal_name',
            FilterInput::text(property: 'legal_name', propertyCase: PropertyCase::Camel)->resolveProperty('company_name'),
        );
    }

    public function testLocalPropertyCaseOverridesTheGlobalOne(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legal_name', FilterInput::text(propertyCase: PropertyCase::Snake)->resolveProperty('legalName'));
    }

    public function testLocalNoneOverridesAGlobalCase(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        self::assertSame('legal_name', FilterInput::text(propertyCase: PropertyCase::None)->resolveProperty('legal_name'));
    }

    public function testAliasDoesNotAffectTheResolvedProperty(): void
    {
        PropertyCaseRegistry::set(PropertyCase::Camel);

        $input = FilterInput::text(alias: 'c');

        self::assertSame('c', $input->alias);
        self::assertSame('legalName', $input->resolveProperty('legal_name'));
    }

    public function testExpressionIsKeptAsDeclared(): void
    {
        $input = FilterInput::text(expression: "CONCAT(u.firstName, ' ', u.lastName)");

        self::assertSame("CONCAT(u.firstName, ' ', u.lastName)", $input->expression);
        self::assertNull($input->alias);
        self::assertNull($input->property);
    }

    /**
     * @return iterable<string, array{\Closure(): FilterInput}>
     */
    public static function conflictingWithExpression(): iterable
    {
        yield 'alias' => [static fn (): FilterInput => FilterInput::text(alias: 'u', expression: 'UNACCENT(u.name)')];
        yield 'property' => [static fn (): FilterInput => FilterInput::text(property: 'name', expression: 'UNACCENT(u.name)')];
        yield 'property case' => [static fn (): FilterInput => FilterInput::text(propertyCase: PropertyCase::Camel, expression: 'UNACCENT(u.name)')];
    }

    /**
     * @param \Closure(): FilterInput $declare
     */
    #[DataProvider('conflictingWithExpression')]
    public function testExpressionCannotBeCombinedWithAliasPropertyOrCase(\Closure $declare): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $declare();
    }

    public function testEveryFactoryAcceptsAnExpression(): void
    {
        foreach (['text', 'bool', 'int', 'float', 'array', 'number', 'date'] as $factory) {
            self::assertSame('u.x', FilterInput::$factory(expression: 'u.x')->expression, $factory);
        }
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
        self::assertNull($input->alias);
        self::assertNull($input->property);
    }

    public function testTextFactoryAcceptsAliasAndProperty(): void
    {
        $input = FilterInput::text('u', 'name');

        self::assertSame('u', $input->alias);
        self::assertSame('name', $input->property);
    }

    public function testNumberFactoryCreatesNumberType(): void
    {
        $input = FilterInput::number();

        self::assertSame(FilterCastType::Number, $input->type);
        self::assertNull($input->alias);
        self::assertNull($input->property);
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
        self::assertNull($input->alias);
        self::assertNull($input->property);
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

    public function testNumberFactoryAcceptsAliasAndProperty(): void
    {
        $input = FilterInput::number(alias: 'p', property: 'price');

        self::assertSame('p', $input->alias);
        self::assertSame('price', $input->property);
    }

    public function testDateFactoryAcceptsAliasAndProperty(): void
    {
        $input = FilterInput::date(alias: 'u', property: 'createdAt');

        self::assertSame('u', $input->alias);
        self::assertSame('createdAt', $input->property);
    }
}
