<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests;

use Letkode\QueryFilterBundle\Filter\PropertyCase;
use Letkode\QueryFilterBundle\Filter\PropertyCaseRegistry;
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

    public function testPropertyCaseDefaultsToNone(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));
        new LetkodeQueryFilterBundle()->getContainerExtension()?->load([[]], $container);

        self::assertSame('none', $container->getParameter('letkode_query_filter.property_case'));
    }

    public function testBootPublishesTheConfiguredPropertyCase(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));
        $bundle = new LetkodeQueryFilterBundle();
        $bundle->getContainerExtension()?->load([['property_case' => 'camel']], $container);
        $bundle->setContainer($container);

        try {
            $bundle->boot();

            self::assertSame(PropertyCase::Camel, PropertyCaseRegistry::get());
        } finally {
            PropertyCaseRegistry::set(PropertyCase::None);
        }
    }

    public function testAnUnknownPropertyCaseIsRejected(): void
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));

        $this->expectException(\Symfony\Component\Config\Definition\Exception\InvalidConfigurationException::class);

        new LetkodeQueryFilterBundle()->getContainerExtension()?->load([['property_case' => 'pascal']], $container);
    }
}
