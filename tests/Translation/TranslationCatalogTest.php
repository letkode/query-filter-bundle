<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests\Translation;

use Letkode\QueryFilterBundle\Exception\RejectionReason;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

/**
 * The bundle ships a default `query_filter` catalog so consumers get
 * usable messages out of the box; an app can override any key.
 */
final class TranslationCatalogTest extends TestCase
{
    /**
     * @return list<array{string}>
     */
    public static function localeProvider(): array
    {
        return [['en'], ['es']];
    }

    #[DataProvider('localeProvider')]
    public function testCatalogDefinesEveryRejectionReason(string $locale): void
    {
        $path = \dirname(__DIR__, 2) . '/translations/query_filter.' . $locale . '.yaml';

        self::assertFileExists($path);

        $contents = file_get_contents($path);
        self::assertIsString($contents);

        foreach (RejectionReason::cases() as $reason) {
            self::assertStringContainsString(
                'query_filter.' . $reason->value . ':',
                $contents,
                \sprintf('Missing key for %s in %s catalog', $reason->value, $locale),
            );
        }

        self::assertStringContainsString('%value%', $contents);
    }
}
