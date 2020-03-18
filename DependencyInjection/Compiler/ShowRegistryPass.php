<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\DependencyInjectionExtension;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ShowRegistryPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ShowRegistryPass implements CompilerPassInterface
{
    /**
     * {@inheritdoc}
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition(DependencyInjectionExtension::class)) {
            return;
        }

        $types = array();
        foreach ($container->findTaggedServiceIds('ekyna_admin.show.type') as $serviceId => $tag) {
            if (!isset($tag[0]['alias'])) {
                throw new InvalidArgumentException(
                    "Attribute 'alias' is missing on tag 'ekyna_admin.show.type' for service '$serviceId'."
                );
            }
            $types[$tag[0]['alias']] = $serviceId;
        }

        $container
            ->getDefinition(DependencyInjectionExtension::class)
            ->replaceArgument(1, $types);
    }
}
