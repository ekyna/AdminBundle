<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Registry;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class DashboardWidgetRegistryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardWidgetRegistryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(Registry::class)) {
            return;
        }

        $types = array();
        foreach ($container->findTaggedServiceIds('ekyna_admin.dashboard.widget_type') as $serviceId => $tag) {
            $alias = isset($tag[0]['alias']) ? $tag[0]['alias'] : $serviceId;
            $types[$alias] = new Reference($serviceId);
        }

        $container
            ->getDefinition(Registry::class)
            ->replaceArgument(0, $types)
        ;
    }
}
