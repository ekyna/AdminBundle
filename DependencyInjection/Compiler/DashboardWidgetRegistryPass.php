<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function call_user_func;

/**
 * Class DashboardWidgetRegistryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardWidgetRegistryPass implements CompilerPassInterface
{
    private const WIDGET_TAG = 'ekyna_admin.dashboard_widget';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container)
    {
        $types = [];
        foreach ($container->findTaggedServiceIds(self::WIDGET_TAG, true) as $serviceId => $tags) {
            $name = call_user_func([$container->getDefinition($serviceId)->getClass(), 'getName']);

            $types[$name] = new Reference($serviceId);
        }

        $container
            ->getDefinition('ekyna_admin.dashboard.widget_registry')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $types, 'admin_dashboard_widgets'));
    }
}
