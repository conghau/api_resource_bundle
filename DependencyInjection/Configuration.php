<?php

namespace conghau\Bundle\ApiResource\DependencyInjection;

use conghau\Bundle\ApiResourceBundle\Constant\ApiType;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tch_api_resource');

        $rootNode
            ->children()
                ->arrayNode('resources')
                    ->useAttributeAsKey('name', true)
                    ->prototype('array')
                        ->children()
                            ->scalarNode('actions')->defaultValue(ApiType::API_ACTIONS_DEFAULT)->end()
                            ->scalarNode('entity')->cannotBeEmpty()->end()
                        ->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
