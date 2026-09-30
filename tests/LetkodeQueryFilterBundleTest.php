<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests;

use Letkode\QueryFilterBundle\LetkodeQueryFilterBundle;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;

final class LetkodeQueryFilterBundleTest extends TestCase
{
    public function testExtensionAliasAndBundlePath(): void
    {
        $bundle = new LetkodeQueryFilterBundle();

        self::assertSame('letkode_query_filter', $bundle->getContainerExtension()?->getAlias());
        self::assertDirectoryExists($bundle->getPath() . '/translations');
    }

    public function testTheExtensionLoadsIntoAContainerWithoutImportingMissingFiles(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));
        $extension = new LetkodeQueryFilterBundle()->getContainerExtension();
        self::assertNotNull($extension);

        $extension->load([[]], $container);

        // The bundle only ships DTOs, exceptions and translations: it registers no services of its own.
        self::assertSame(['service_container'], array_keys($container->getDefinitions()));
    }

    public function testTheContainerCompilesWithTheBundleLoaded(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));
        new LetkodeQueryFilterBundle()->getContainerExtension()?->load([[]], $container);

        $container->compile();

        self::assertTrue($container->isCompiled());
    }
}
