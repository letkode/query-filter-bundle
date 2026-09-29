<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests;

use Letkode\QueryFilterBundle\LetkodeQueryFilterBundle;
use PHPUnit\Framework\TestCase;

final class LetkodeQueryFilterBundleTest extends TestCase
{
    public function testExtensionAliasAndBundlePath(): void
    {
        $bundle = new LetkodeQueryFilterBundle();

        self::assertSame('letkode_query_filter', $bundle->getContainerExtension()?->getAlias());
        self::assertDirectoryExists($bundle->getPath() . '/translations');
    }
}
