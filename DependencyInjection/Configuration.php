<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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
     * @inheritDoc
     */
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $builder = new TreeBuilder('ekyna_admin');

        $root = $builder->getRootNode();
        $root
            ->children()
                ->scalarNode('routing_prefix')->defaultValue('/administration')->end()
                // TODO 'ui' section
                ->scalarNode('logo_path')->defaultValue('bundles/ekynaadmin/img/logo.png')->end()
                ->arrayNode('stylesheets')
                    ->prototype('scalar')
                    ->treatNullLike([])
                    ->defaultValue([])
                ->end()
            ->end()
        ;

        $this->addDashboardSection($root);
        $this->addMenusSection($root);
        $this->addNavBarSection($root);
        $this->addSecuritySection($root);
        $this->addShowSection($root);

        return $builder;
    }

    /**
     * Adds the `nav bar` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addNavBarSection(ArrayNodeDefinition $node): void
    {
        $node
            ->children()
                ->arrayNode('navbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('background')
                            ->defaultValue('#2c3742')
                        ->end()
                        ->scalarNode('logo')
                            ->defaultValue('bundles/ekynaadmin/img/logo.png')
                        ->end()
                        ->booleanNode('light')
                            ->defaultFalse()
                        ->end()
                        ->arrayNode('buttons')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->addDefaultsIfNotSet()
                                ->children()
                                    ->scalarNode('route')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('icon')->isRequired()->cannotBeEmpty()->end()
                                    ->scalarNode('title')->defaultValue('')->end()
                                    ->scalarNode('domain')->defaultNull()->end()
                                    ->scalarNode('target')->defaultValue('_blank')->end()
                                    ->integerNode('position')->defaultValue(50)->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the `menus` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addMenusSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('menus')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('label')->isRequired()->cannotBeEmpty()->end()
                            ->scalarNode('icon')->isRequired()->cannotBeEmpty()->end()
                            ->integerNode('position')->defaultValue(0)->end()
                            ->scalarNode('domain')->defaultNull()->end()
                            ->scalarNode('route')->defaultNull()->end()
                            ->arrayNode('entries')
                                ->useAttributeAsKey('name')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('label')->defaultNull()->end()
                                        ->scalarNode('domain')->defaultNull()->end()
                                        ->integerNode('position')->defaultValue(0)->end()
                                        ->scalarNode('route')->defaultNull()->end()
                                        ->scalarNode('resource')->defaultNull()->end()
                                        ->scalarNode('action')->defaultNull()->end()
                                    ->end()
                                ->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the `dashboard` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addDashboardSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('dashboard')
                    ->useAttributeAsKey('name')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('type')->isRequired()->cannotBeEmpty()->end()
                            ->arrayNode('options')
                                ->useAttributeAsKey('name')
                                ->prototype('scalar')->end()
                                ->defaultValue([])
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the `security` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addSecuritySection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('security')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('remember_me')->defaultValue('_admin_remember_me')->end()
                        ->arrayNode('notification')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->booleanNode('admin_login')->defaultTrue()->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * Adds the `show` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addShowSection(ArrayNodeDefinition $node)
    {
        $node
            ->children()
                ->arrayNode('show')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('default_template')->defaultValue('@EkynaAdmin/Show/show_div_layout.html.twig')->end()
                        ->arrayNode('templates')
                            ->defaultValue([])
                            ->scalarPrototype()->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
