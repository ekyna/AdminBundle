<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\ServiceLocatorTagPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

use function call_user_func;

/**
 * Class ShowRegistryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShowRegistryPass implements CompilerPassInterface
{
    private const TYPE_TAG = 'ekyna_admin.show.type';

    /**
     * @inheritDoc
     */
    public function process(ContainerBuilder $container): void
    {
        $types = [];
        foreach ($container->findTaggedServiceIds(self::TYPE_TAG, true) as $serviceId => $tag) {
            $name = call_user_func([$container->getDefinition($serviceId)->getClass(), 'getName']);
            $types[$name] = new Reference($serviceId);
        }

        $container
            ->getDefinition('ekyna_admin.show.dependency_injection_extension')
            ->replaceArgument(0, ServiceLocatorTagPass::register($container, $types, 'admin_show_types'));
    }
}
