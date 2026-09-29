<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle\Tests;

use Letkode\QueryFilterBundle\EventListener\UndeclaredQueryParameterListener;
use Letkode\QueryFilterBundle\LetkodeQueryFilterBundle;
use Letkode\QueryFilterBundle\Tests\Fixtures\FakeTranslator;
use PHPUnit\Framework\TestCase;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBag;
use Symfony\Component\EventDispatcher\DependencyInjection\RegisterListenersPass;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Contracts\Translation\TranslatorInterface;

final class LetkodeQueryFilterBundleTest extends TestCase
{
    private function load(): ContainerBuilder
    {
        $container = new ContainerBuilder(new ParameterBag(['kernel.debug' => true]));
        $extension = new LetkodeQueryFilterBundle()->getContainerExtension();
        self::assertNotNull($extension);

        $extension->load([[]], $container);

        return $container;
    }

    public function testExtensionAliasAndBundlePath(): void
    {
        $bundle = new LetkodeQueryFilterBundle();

        self::assertSame('letkode_query_filter', $bundle->getContainerExtension()?->getAlias());
        self::assertFileExists($bundle->getPath() . '/config/services.yaml');
        self::assertDirectoryExists($bundle->getPath() . '/translations');
    }

    public function testListenerIsTaggedOnKernelExceptionBeforeDefaultPriorityListeners(): void
    {
        $tags = $this->load()->getDefinition(UndeclaredQueryParameterListener::class)->getTag('kernel.event_listener');

        self::assertCount(1, $tags);
        self::assertSame('kernel.exception', $tags[0]['event']);
        self::assertSame('__invoke', $tags[0]['method']);
        self::assertGreaterThan(0, $tags[0]['priority'], 'Must run before the response-rendering listeners (priority 0).');
    }

    public function testContainerCompilesAndRegistersTheListenerOnTheDispatcher(): void
    {
        $container = $this->load();
        $container->register('event_dispatcher', EventDispatcher::class)->setPublic(true);
        $container->register('translator', FakeTranslator::class);
        $container->setAlias(TranslatorInterface::class, 'translator');
        $container->addCompilerPass(new RegisterListenersPass());
        $container->compile();

        $priorities = [];
        foreach ($container->getDefinition('event_dispatcher')->getMethodCalls() as [$method, $arguments]) {
            if ('addListener' === $method && 'kernel.exception' === $arguments[0]) {
                $priorities[] = $container->getParameterBag()->resolveValue($arguments[2]);
            }
        }

        self::assertCount(1, $priorities, 'The listener must be registered exactly once on kernel.exception.');
        self::assertGreaterThan(0, $priorities[0]);
    }
}
