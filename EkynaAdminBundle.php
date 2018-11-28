<?php

namespace Ekyna\Bundle\AdminBundle;

use Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler as Pass;
use Ekyna\Bundle\AdminBundle\Model as Model;
use Ekyna\Bundle\ResourceBundle\AbstractBundle;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaAdminBundle
 * @package Ekyna\Bundle\AdminBundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminBundle extends AbstractBundle
{
    /**
     * {@inheritdoc}
     */
    public function build(ContainerBuilder $container)
    {
        parent::build($container);

        $container->addCompilerPass(new Pass\AdminMenuPass());
        $container->addCompilerPass(new Pass\DashboardWidgetRegistryPass());
        $container->addCompilerPass(new Pass\ShowRegistryPass());
    }

    /**
     * @inheritdoc
     */
    protected function getModelInterfaces()
    {
        return [
            Model\UserInterface::class  => 'ekyna_admin.user.class',
            Model\GroupInterface::class => 'ekyna_admin.group.class',
        ];
    }
}
