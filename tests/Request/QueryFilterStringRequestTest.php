<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Request;

use Letkode\QueryFilterBundle\Request\QueryFilterStringRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class QueryFilterStringRequestTest extends TestCase
{
    public function testDefaultsAreValid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest());

        self::assertCount(0, $violations);
    }

    public function testPageMustBePositive(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(page: 0));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('page', $violations[0]->getPropertyPath());
    }

    public function testPageMustNotBeNegative(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(page: -1));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testPerPageMustNotExceed100(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(perPage: 101));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('perPage', $violations[0]->getPropertyPath());
    }

    public function testPerPageMustBeAtLeast1(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(perPage: 0));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testDirMustBeAscOrDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(dir: 'random'));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('dir', $violations[0]->getPropertyPath());
    }

    public function testDirAllowsDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryFilterStringRequest(dir: 'desc'));

        self::assertCount(0, $violations);
    }

    public function testFiltersDefaultToEmptyArray(): void
    {
        self::assertSame([], new QueryFilterStringRequest()->filters);
    }

    public function testFiltersAcceptsRawNestedArray(): void
    {
        $raw = ['firstName' => [['op' => 'is', 'value' => ['x']]]];

        self::assertSame($raw, new QueryFilterStringRequest(filters: $raw)->filters);
    }
}
