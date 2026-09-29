<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Integration;

use Letkode\HttpExceptionBundle\Contract\LocaleResolverInterface;
use Letkode\HttpExceptionBundle\EventListener\ExceptionListener;
use Letkode\QueryFilterBundle\Exception\QueryParameterRejection;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Exception\UndeclaredQueryParameterException;
use Letkode\QueryFilterBundle\Tests\Fixtures\FakeTranslator;
use PHPUnit\Framework\TestCase;
use Psr\Log\NullLogger;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\HttpKernelInterface;

/**
 * The exception is meant to be answered by letkode/http-exception-bundle's listener; this pins the
 * response an API client actually receives.
 */
final class RenderedByTheHttpExceptionListenerTest extends TestCase
{
    public function testAnUndeclaredQueryParameterIsAnswered422WithTranslatedErrorsByParameter(): void
    {
        $translator = new FakeTranslator([
            'query_filter|The query contains undeclared or invalid parameters.' => 'La consulta contiene parámetros no declarados o inválidos.',
            'query_filter|query_filter.not_sortable' => 'No se permite ordenar por «secret».',
            'query_filter|query_filter.not_filterable' => 'No se permite filtrar por «etapa».',
            'query_filter|query_filter.unknown_operator' => 'El operador «zz» no es válido.',
        ]);
        $locale = new class implements LocaleResolverInterface {
            public function resolve(): string
            {
                return 'es';
            }
        };
        $listener = new ExceptionListener($translator, new NullLogger(), $locale, false, '/api');

        $event = new ExceptionEvent(
            $this->createStub(HttpKernelInterface::class),
            Request::create('/api/things?sort=secret'),
            HttpKernelInterface::MAIN_REQUEST,
            new UndeclaredQueryParameterException([
                new QueryParameterRejection('sort', RejectionReason::NotSortable, 'secret'),
                new QueryParameterRejection('filters.etapa', RejectionReason::NotFilterable, 'etapa'),
                new QueryParameterRejection('filters.etapa', RejectionReason::UnknownOperator, 'zz'),
            ]),
        );

        $listener($event);

        $response = $event->getResponse();
        self::assertNotNull($response);
        self::assertSame(422, $response->getStatusCode());
        self::assertSame(
            [
                'success' => false,
                'message' => 'La consulta contiene parámetros no declarados o inválidos.',
                'status' => 422,
                'errorCode' => 'INVALID_QUERY_PARAMETERS',
                'errors' => [
                    'sort' => ['No se permite ordenar por «secret».'],
                    'filters.etapa' => ['No se permite filtrar por «etapa».', 'El operador «zz» no es válido.'],
                ],
            ],
            json_decode((string) $response->getContent(), true, flags: \JSON_THROW_ON_ERROR),
        );
    }
}
