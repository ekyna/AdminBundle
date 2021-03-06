<?php

namespace Ekyna\Bundle\AdminBundle;

use Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler as Pass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaAdminBundle
 * @package Ekyna\Bundle\AdminBundle
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminBundle extends Bundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Pass\ResourceRegistryPass());
        $container->addCompilerPass(new Pass\DashboardWidgetTypeRegistryPass());
    }
}
