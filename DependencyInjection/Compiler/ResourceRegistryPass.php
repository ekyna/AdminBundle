<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ResourceRegistryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRegistryPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.pool_registry')) {
            return;
        }

        $definition = $container->getDefinition('ekyna_admin.pool_registry');

        $configurations = array();
        foreach ($container->findTaggedServiceIds('ekyna_admin.configuration') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $serviceId;
            $configurations[$alias] = new Reference($serviceId);
        }
        $definition->replaceArgument(0, $configurations);
    }
}
