<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Exception;

use Letkode\QueryFilterBundle\Exception\RejectionMessage;
use Letkode\QueryFilterBundle\Exception\RejectionReason;
use Letkode\QueryFilterBundle\Tests\Fixtures\FakeTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Contracts\Translation\TranslatableInterface;

final class RejectionMessageTest extends TestCase
{
    public function testItIsTranslatable(): void
    {
        self::assertInstanceOf(TranslatableInterface::class, new RejectionMessage(RejectionReason::NotSortable, 'x'));
    }

    public function testTranslatesTheReasonKeyInTheQueryFilterDomainWithTheValueAndLocale(): void
    {
        $translator = new FakeTranslator(['query_filter|query_filter.not_filterable' => 'No se permite filtrar por «etapa».']);

        $text = new RejectionMessage(RejectionReason::NotFilterable, 'etapa')->trans($translator, 'es');

        self::assertSame('No se permite filtrar por «etapa».', $text);
        self::assertSame(
            [['id' => 'query_filter.not_filterable', 'parameters' => ['%value%' => 'etapa'], 'domain' => 'query_filter', 'locale' => 'es']],
            $translator->calls,
        );
    }

    public function testAMissingValueIsTranslatedAsAnEmptyString(): void
    {
        $translator = new FakeTranslator();

        new RejectionMessage(RejectionReason::MalformedFilter)->trans($translator);

        self::assertSame(['%value%' => ''], $translator->calls[0]['parameters']);
        self::assertNull($translator->calls[0]['locale']);
    }

    public function testEachReasonUsesItsOwnKey(): void
    {
        $translator = new FakeTranslator();

        foreach (RejectionReason::cases() as $reason) {
            new RejectionMessage($reason, 'v')->trans($translator);
        }

        self::assertSame(
            array_map(static fn (RejectionReason $reason): string => 'query_filter.' . $reason->value, RejectionReason::cases()),
            array_column($translator->calls, 'id'),
        );
    }
}
