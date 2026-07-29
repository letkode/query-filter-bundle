<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Request;

use Letkode\QueryFilterBundle\Request\FilterQueryStringRequest;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Validator\Validation;

final class FilterQueryStringRequestTest extends TestCase
{
    public function testDefaultsAreValid(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest());

        self::assertCount(0, $violations);
    }

    public function testPageMustBePositive(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(page: 0));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('page', $violations[0]->getPropertyPath());
    }

    public function testPageMustNotBeNegative(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(page: -1));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testPerPageMustNotExceed100(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(perPage: 101));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('perPage', $violations[0]->getPropertyPath());
    }

    public function testPerPageMustBeAtLeast1(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(perPage: 0));

        self::assertGreaterThan(0, \count($violations));
    }

    public function testDirMustBeAscOrDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(dir: 'random'));

        self::assertGreaterThan(0, \count($violations));
        self::assertSame('dir', $violations[0]->getPropertyPath());
    }

    public function testDirAllowsDesc(): void
    {
        $validator = Validation::createValidatorBuilder()->enableAttributeMapping()->getValidator();

        $violations = $validator->validate(new FilterQueryStringRequest(dir: 'desc'));

        self::assertCount(0, $violations);
    }

    public function testFiltersDefaultToEmptyArray(): void
    {
        self::assertSame([], new FilterQueryStringRequest()->filters);
    }

    public function testFiltersAcceptsRawNestedArray(): void
    {
        $raw = ['firstName' => [['op' => 'is', 'value' => ['x']]]];

        self::assertSame($raw, new FilterQueryStringRequest(filters: $raw)->filters);
    }
}
