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
                ->scalarNode('logo_path')->defaultValue('/bundles/ekynaadmin/img/logo.png')->end()
                ->scalarNode('output_dir')->defaultValue('')->end()
                ->append($this->getResourcesSection())
                ->append($this->getMenusSection())
                ->arrayNode('css_inputs')
                    ->prototype('scalar')
                    ->treatNullLike(array())
                    ->defaultValue(array())
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }

    /**
     * Returns the resources configuration definition.
     *
     * @return \Symfony\Component\Config\Definition\Builder\NodeDefinition
     */
    private function getResourcesSection()
    {
        $builder = new TreeBuilder();
        $node = $builder->root('resources');

        $node
            ->useAttributeAsKey('prefix')
            ->prototype('array')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->variableNode('templates')->end() // TODO normalization ?
                        ->scalarNode('entity')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('controller')->end()
                        ->scalarNode('repository')->end()
                        ->scalarNode('operator')->end()
                        ->scalarNode('form')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('table')->isRequired()->cannotBeEmpty()->end()
                        ->scalarNode('parent')->end()
                        ->scalarNode('event')->end()
                        ->arrayNode('translation')
                            ->children()
                                ->scalarNode('entity')->end()
                                ->scalarNode('repository')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
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
                    ->arrayNode('entries')
                        ->useAttributeAsKey('name')
                        ->prototype('array')
                            ->children()
                                ->scalarNode('route')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                                ->scalarNode('resource')->isRequired()->cannotBeEmpty()->end()
                                ->integerNode('position')->defaultValue(0)->end()
                                ->scalarNode('domain')->defaultNull()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $node;
    }
}
