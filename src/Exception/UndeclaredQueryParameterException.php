<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Exception;

use Letkode\HttpExceptionBundle\Exception\AbstractHttpStatusException;
use Letkode\HttpExceptionBundle\Option\ErrorsOption;
use Letkode\HttpExceptionBundle\Option\TranslationOption;
use Symfony\Component\HttpFoundation\Response;

/**
 * Thrown when a query carries parameters that are not declared by the
 * consumer (unknown sort/filter field, unsupported operator) or are
 * structurally malformed.
 *
 * Carries the full list of rejections so the caller can report them all at
 * once. As an HTTP status exception it is answered with a 422 whose `errors`
 * are keyed by the rejected parameter (`sort`, `filters.etapa`) and whose
 * messages come from the `query_filter` translation domain.
 */
class UndeclaredQueryParameterException extends AbstractHttpStatusException
{
    /**
     * @param non-empty-list<QueryParameterRejection> $rejections
     */
    public function __construct(public readonly array $rejections)
    {
        if ([] === $rejections) {
            throw new \InvalidArgumentException('UndeclaredQueryParameterException requires at least one rejection.');
        }

        $errors = [];
        foreach ($rejections as $rejection) {
            $errors[$rejection->parameter][] = new RejectionMessage($rejection->reason, $rejection->value);
        }

        parent::__construct(
            'The query contains undeclared or invalid parameters.',
            options: [new TranslationOption(domain: 'query_filter'), new ErrorsOption($errors)],
        );
    }

    public function getStatusCode(): int
    {
        return Response::HTTP_UNPROCESSABLE_ENTITY;
    }

    protected function defaultErrorCode(): string
    {
        return 'INVALID_QUERY_PARAMETERS';
    }
}
