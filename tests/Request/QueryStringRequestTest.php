<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Request;

use Letkode\QueryFilterBundle\Request\QueryStringRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class QueryStringRequestTest extends TestCase
{
    public function testDefaultsAreValid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest());

        self::assertCount(0, $violations);
    }

    public function testPageMustBePositive(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(page: 0));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('page', $violations[0]->getPropertyPath());
    }

    public function testPageMustNotBeNegative(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(page: -1));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testPerPageMustNotExceed100(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(perPage: 101));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('perPage', $violations[0]->getPropertyPath());
    }

    public function testPerPageMustBeAtLeast1(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(perPage: 0));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testDirMustBeAscOrDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(dir: 'random'));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('dir', $violations[0]->getPropertyPath());
    }

    public function testDirAllowsDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new QueryStringRequest(dir: 'desc'));

        self::assertCount(0, $violations);
    }

    public function testFiltersDefaultToEmptyArray(): void
    {
        self::assertSame([], new QueryStringRequest()->filters);
    }

    public function testFiltersAcceptsRawNestedArray(): void
    {
        $raw = ['firstName' => [['op' => 'is', 'value' => ['x']]]];

        self::assertSame($raw, new QueryStringRequest(filters: $raw)->filters);
    }
}
