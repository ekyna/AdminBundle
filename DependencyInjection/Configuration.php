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
            ->end()
        ;

        return $treeBuilder;
    }
}
