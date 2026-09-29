<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\EventListener;

use Letkode\QueryFilterBundle\Exception\UndeclaredQueryParameterException;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Exception\ValidationFailedException;
use Symfony\Contracts\Translation\TranslatorInterface;

/**
 * Turns an UndeclaredQueryParameterException into the same 422 Symfony itself produces for an invalid
 * request payload: an UnprocessableEntityHttpException wrapping a ValidationFailedException, with one
 * violation per rejected parameter.
 *
 * The exception stays HTTP-agnostic; this listener only converts it. Rendering the response is left to
 * whichever exception listener the application uses, which already understands that shape.
 */
final readonly class UndeclaredQueryParameterListener
{
    public function __construct(private TranslatorInterface $translator)
    {
    }

    public function __invoke(ExceptionEvent $event): void
    {
        $exception = $event->getThrowable();

        if (!$exception instanceof UndeclaredQueryParameterException) {
            return;
        }

        $violations = new ConstraintViolationList();

        foreach ($exception->rejections as $rejection) {
            $key = 'query_filter.' . $rejection->reason->value;
            $parameters = ['%value%' => $rejection->value ?? ''];

            $violations->add(new ConstraintViolation(
                $this->translator->trans($key, $parameters, 'query_filter'),
                $key,
                $parameters,
                null,
                $rejection->parameter,
                $rejection->value,
            ));
        }

        $event->setThrowable(new UnprocessableEntityHttpException(
            $exception->getMessage(),
            new ValidationFailedException(null, $violations),
        ));
    }
}
