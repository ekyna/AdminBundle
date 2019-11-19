<?php

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
     * @inheritdoc
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ekyna_admin');

        /** @noinspection PhpUndefinedMethodInspection */
        $rootNode
            ->children()
                ->scalarNode('logo_path')->defaultValue('bundles/ekynaadmin/img/logo.png')->end()
                ->arrayNode('notification')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->booleanNode('admin_login')->defaultTrue()->end()
                    ->end()
                ->end()
                ->arrayNode('stylesheets')
                    ->prototype('scalar')
                    ->treatNullLike([])
                    ->defaultValue([])
                ->end()
            ->end()
        ;

        $this->addNavBarSection($rootNode);
        $this->addMenusSection($rootNode);
        $this->addDashboardSection($rootNode);
        $this->addShowSection($rootNode);
        $this->addPoolsSection($rootNode);

        return $treeBuilder;
    }

    /**
     * Adds the `nav bar` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addNavBarSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('navbar')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('buttons')
                            ->useAttributeAsKey('name')
                            ->prototype('array')
                                ->addDefaultsIfNotSet()
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
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('menus')
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
        /** @noinspection PhpUndefinedMethodInspection */
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
                                ->defaultValue(array())
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
        /** @noinspection PhpUndefinedMethodInspection */
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

    /**
     * Adds `pools` section.
     *
     * @param ArrayNodeDefinition $node
     */
    private function addPoolsSection(ArrayNodeDefinition $node)
    {
        /** @noinspection PhpUndefinedMethodInspection */
        $node
            ->children()
                ->arrayNode('pools')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->arrayNode('user')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaAdmin/Admin/User/_form.html',
                                    'show.html'  => '@EkynaAdmin/Admin/User/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\AdminBundle\Entity\User')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\AdminBundle\Controller\Admin\UserController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\AdminBundle\Repository\UserRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\AdminBundle\Form\Type\UserType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\AdminBundle\Table\Type\UserType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                        ->arrayNode('group')
                            ->addDefaultsIfNotSet()
                            ->children()
                                ->variableNode('templates')->defaultValue([
                                    '_form.html' => '@EkynaAdmin/Admin/Group/_form.html',
                                    'show.html'  => '@EkynaAdmin/Admin/Group/show.html',
                                ])->end()
                                ->scalarNode('entity')->defaultValue('Ekyna\Bundle\AdminBundle\Entity\Group')->end()
                                ->scalarNode('controller')->defaultValue('Ekyna\Bundle\AdminBundle\Controller\Admin\GroupController')->end()
                                ->scalarNode('repository')->defaultValue('Ekyna\Bundle\AdminBundle\Repository\GroupRepository')->end()
                                ->scalarNode('form')->defaultValue('Ekyna\Bundle\AdminBundle\Form\Type\GroupType')->end()
                                ->scalarNode('table')->defaultValue('Ekyna\Bundle\AdminBundle\Table\Type\GroupType')->end()
                                ->scalarNode('parent')->end()
                                ->scalarNode('event')->end()
                            ->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
