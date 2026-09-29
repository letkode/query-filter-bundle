<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\EventListener;

use Letkode\QueryFilterBundle\EventListener\UndeclaredQueryParameterListener;
use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Exception\UndeclaredQueryParameterException;
use Letkode\QueryFilterBundle\Tests\Fixtures\FakeTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Symfony\Component\Validator\Exception\ValidationFailedException;

final class UndeclaredQueryParameterListenerTest extends TestCase
{
    private function dispatch(\Throwable $throwable, FakeTranslator|null $translator = null): ExceptionEvent
    {
        $listener = new UndeclaredQueryParameterListener($translator ?? new FakeTranslator());

        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/api/things'),
            HttpKernelInterface::MAIN_REQUEST,
            $throwable,
        );

        $listener($event);

        return $event;
    }

    private function validationFailure(ExceptionEvent $event): ValidationFailedException
    {
        $converted = $event->getThrowable();
        self::assertInstanceOf(UnprocessableEntityHttpException::class, $converted);

        $previous = $converted->getPrevious();
        self::assertInstanceOf(ValidationFailedException::class, $previous);

        return $previous;
    }

    public function testConvertsToAnUnprocessableEntityWrappingAValidationFailure(): void
    {
        $exception = new UndeclaredQueryParameterException([
            new QueryParameterRejection('sort', RejectionReason::NotSortable, 'secret'),
            new QueryParameterRejection('filters.etapa', RejectionReason::NotFilterable, 'etapa'),
        ]);

        $event = $this->dispatch($exception);
        $converted = $event->getThrowable();

        self::assertInstanceOf(UnprocessableEntityHttpException::class, $converted);
        self::assertSame(422, $converted->getStatusCode());
        self::assertSame($exception->getMessage(), $converted->getMessage());
        self::assertNull($event->getResponse(), 'The listener must only convert, not render.');

        $violations = $this->validationFailure($event)->getViolations();
        self::assertCount(2, $violations);
        self::assertSame('sort', $violations->get(0)->getPropertyPath());
        self::assertSame('filters.etapa', $violations->get(1)->getPropertyPath());
    }

    public function testViolationMessageIsTheTranslatedReasonInTheQueryFilterDomain(): void
    {
        $translator = new FakeTranslator(['query_filter|query_filter.not_sortable' => 'No se permite ordenar por «secret».']);
        $exception = new UndeclaredQueryParameterException([
            new QueryParameterRejection('sort', RejectionReason::NotSortable, 'secret'),
        ]);

        $violation = $this->validationFailure($this->dispatch($exception, $translator))->getViolations()->get(0);

        self::assertSame('No se permite ordenar por «secret».', $violation->getMessage());
        self::assertSame('query_filter.not_sortable', $violation->getMessageTemplate());
        self::assertSame(['%value%' => 'secret'], $violation->getParameters());
        self::assertSame('secret', $violation->getInvalidValue());
        self::assertSame(
            [['id' => 'query_filter.not_sortable', 'parameters' => ['%value%' => 'secret'], 'domain' => 'query_filter', 'locale' => null]],
            $translator->calls,
        );
    }

    public function testEveryReasonUsesItsOwnTranslationKey(): void
    {
        $rejections = array_map(
            static fn (RejectionReason $reason): QueryParameterRejection => new QueryParameterRejection('p.' . $reason->value, $reason, 'v'),
            RejectionReason::cases(),
        );

        $translator = new FakeTranslator();
        $this->dispatch(new UndeclaredQueryParameterException($rejections), $translator);

        self::assertSame(
            array_map(static fn (RejectionReason $reason): string => 'query_filter.' . $reason->value, RejectionReason::cases()),
            array_column($translator->calls, 'id'),
        );
    }

    public function testSeveralRejectionsOfTheSameParameterStaySeparateViolations(): void
    {
        $exception = new UndeclaredQueryParameterException([
            new QueryParameterRejection('filters.etapa', RejectionReason::NotFilterable, 'etapa'),
            new QueryParameterRejection('filters.etapa', RejectionReason::UnknownOperator, 'zz'),
        ]);

        $violations = $this->validationFailure($this->dispatch($exception))->getViolations();

        self::assertCount(2, $violations);
        self::assertSame('filters.etapa', $violations->get(0)->getPropertyPath());
        self::assertSame('filters.etapa', $violations->get(1)->getPropertyPath());
    }

    public function testRejectionWithoutValueTranslatesWithAnEmptyValue(): void
    {
        $translator = new FakeTranslator();
        $exception = new UndeclaredQueryParameterException([
            new QueryParameterRejection('filters.etapa', RejectionReason::MalformedFilter),
        ]);

        $violation = $this->validationFailure($this->dispatch($exception, $translator))->getViolations()->get(0);

        self::assertSame(['%value%' => ''], $translator->calls[0]['parameters']);
        self::assertNull($violation->getInvalidValue());
    }

    public function testOtherExceptionsAreLeftUntouched(): void
    {
        $original = new NotFoundHttpException('nope');

        $event = $this->dispatch($original);

        self::assertSame($original, $event->getThrowable());
        self::assertNull($event->getResponse());
    }
}
