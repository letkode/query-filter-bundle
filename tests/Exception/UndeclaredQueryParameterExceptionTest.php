<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Exception;

use Letkode\HttpExceptionBundle\Contract\HttpStatusExceptionInterface;
use Letkode\HttpExceptionBundle\Exception\AbstractHttpStatusException;
use Letkode\HttpExceptionBundle\Option\ErrorsOption;
use Letkode\HttpExceptionBundle\Option\TranslationOption;
use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionMessage;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Exception\UndeclaredQueryParameterException;
use Letkode\QueryFilterBundle\Tests\Fixtures\FakeTranslator;
use PHPUnit\Framework\TestCase;

final class UndeclaredQueryParameterExceptionTest extends TestCase
{
    public function testCarriesTheRejectionList(): void
    {
        $rejections = [
            new QueryParameterRejection('sort', RejectionReason::NotSortable, 'invented'),
            new QueryParameterRejection('filters.stage', RejectionReason::NotFilterable, 'stage'),
        ];

        $exception = new UndeclaredQueryParameterException($rejections);

        self::assertSame($rejections, $exception->rejections);
        self::assertSame('The query contains undeclared or invalid parameters.', $exception->getMessage());
    }

    public function testRejectsAnEmptyList(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        /* @phpstan-ignore argument.type (deliberately violating the non-empty contract) */
        new UndeclaredQueryParameterException([]);
    }

    public function testIsAnHttpStatusExceptionWithAnUnprocessableStatusAndItsOwnErrorCode(): void
    {
        $exception = new UndeclaredQueryParameterException([new QueryParameterRejection('sort', RejectionReason::NotSortable, 'x')]);

        self::assertInstanceOf(AbstractHttpStatusException::class, $exception);
        self::assertInstanceOf(HttpStatusExceptionInterface::class, $exception);
        self::assertSame(422, $exception->getStatusCode());
        self::assertSame('INVALID_QUERY_PARAMETERS', $exception->getErrorCode());
    }

    public function testIsNotFinal(): void
    {
        self::assertFalse(new \ReflectionClass(UndeclaredQueryParameterException::class)->isFinal());
    }

    public function testExposesTheRejectionsAsErrorsByParameter(): void
    {
        $exception = new UndeclaredQueryParameterException([
            new QueryParameterRejection('sort', RejectionReason::NotSortable, 'invented'),
            new QueryParameterRejection('filters.stage', RejectionReason::NotFilterable, 'stage'),
            new QueryParameterRejection('filters.stage', RejectionReason::UnknownOperator, 'zz'),
        ]);

        $errors = $exception->getOption(ErrorsOption::class);

        self::assertNotNull($errors);
        self::assertSame(['sort', 'filters.stage'], array_keys($errors->errors));
        self::assertCount(1, $errors->errors['sort']);
        self::assertCount(2, $errors->errors['filters.stage']);
        self::assertEquals(new RejectionMessage(RejectionReason::NotSortable, 'invented'), $errors->errors['sort'][0]);
        self::assertEquals(new RejectionMessage(RejectionReason::UnknownOperator, 'zz'), $errors->errors['filters.stage'][1]);
    }

    public function testItsMessageIsTranslatedInTheQueryFilterDomain(): void
    {
        $exception = new UndeclaredQueryParameterException([new QueryParameterRejection('sort', RejectionReason::NotSortable, 'x')]);

        $translation = $exception->getOption(TranslationOption::class);

        self::assertNotNull($translation);
        self::assertTrue($translation->isTranslatable);
        self::assertSame('query_filter', $translation->domain);
    }

    public function testEveryErrorMessageTranslatesThroughTheQueryFilterCatalog(): void
    {
        $exception = new UndeclaredQueryParameterException([new QueryParameterRejection('sort', RejectionReason::NotSortable, 'x')]);
        $translator = new FakeTranslator(['query_filter|query_filter.not_sortable' => 'No se permite ordenar por «x».']);

        $errors = $exception->getOption(ErrorsOption::class);
        self::assertNotNull($errors);
        $message = $errors->errors['sort'][0];
        self::assertInstanceOf(RejectionMessage::class, $message);

        self::assertSame('No se permite ordenar por «x».', $message->trans($translator, 'es'));
    }
}
