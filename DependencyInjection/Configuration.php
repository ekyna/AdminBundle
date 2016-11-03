<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * Class Configuration
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_admin');

        $rootNode
            ->children()
                ->scalarNode('logo_path')->defaultValue('bundles/ekynaadmin/img/logo.png')->end()
                /*->scalarNode('output_dir')->defaultValue('')->end()*/
                ->append($this->getMenusSection())
                ->append($this->getDashboardSection())
                ->arrayNode('navbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('buttons')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->children()
                                    ->scalarNode('route')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('icon')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('title')->defaultValue('')->end()
                                    ->scalarNode('target')->defaultValue('_blank')->end()
                                    ->integerNode('position')->defaultValue(50)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('stylesheets')
                    ->prototype('scalar')
                    ->treatNullLike([])
                    ->defaultValue([])
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Returns the menu configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getMenusSection()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('menus');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                    ->scalarNode('icon')->isRequired()->cannotBeEmpty()->end()
                    ->integerNode('position')->defaultValue(0)->end()
                    ->scalarNode('domain')->defaultValue('messages')->end()
                    ->scalarNode('route')->defaultNull()->end()
                    ->arrayNode('entries')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('route')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('resource')->isRequired()->cannotBeEmpty()->end()
                                ->integerNode('position')->defaultValue(0)->end()
                                ->scalarNode('domain')->defaultValue('messages')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }

    /**
     * Returns the dashboard configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getDashboardSection()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('dashboard');

        $node
            ->useAttributeAsKey('name')
            ->prototype('array')
                ->children()
                    ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                    ->arrayNode('options')
                        ->useAttributeAsKey('name')
                        ->prototype('scalar')->end()
                        ->defaultValue(array())
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
