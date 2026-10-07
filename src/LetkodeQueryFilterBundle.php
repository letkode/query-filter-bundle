<?php

declare(strict_types=1);

namespace Letkode\QueryFilterBundle;

use Letkode\QueryFilterBundle\Filter\PropertyCase;
use Letkode\QueryFilterBundle\Filter\PropertyCaseRegistry;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;

final class LetkodeQueryFilterBundle extends AbstractBundle
{
    public function getPath(): string
    {
        return \dirname(__DIR__);
    }

    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->rootNode()
            ->children()
                ->enumNode('property_case')
                    ->values(array_column(PropertyCase::cases(), 'value'))
                    ->defaultValue(PropertyCase::None->value)
                    ->info('Spelling of the fields filters target; a key without an explicit path is converted to it.')
                ->end()
            ->end();
    }

    public function boot(): void
    {
        $case = $this->container?->getParameter('letkode_query_filter.property_case');

        PropertyCaseRegistry::set(\is_string($case) ? PropertyCase::from($case) : PropertyCase::None);
    }

    /**
     * @param array<string, mixed> $config
     */
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->parameters()->set('letkode_query_filter.property_case', $config['property_case']);
    }
}
