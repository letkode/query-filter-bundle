<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Filter;

use Letkode\QueryFilterBundle\Filter\FilterCastType;
use Letkode\QueryFilterBundle\Filter\FilterInput;
use PHPUnit\Framework\TestCase;

final class FilterInputTest extends TestCase
{
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
