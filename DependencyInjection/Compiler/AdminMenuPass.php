<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;

/**
 * Class AdminMenuPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminMenuPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        $pool->addMethodCall('createGroup', [[
            'name'     => 'admin',
            'label'    => 'ekyna_admin.title',
            'icon'     => 'lock',
            'position' => 101,
        ]]);
        $pool->addMethodCall('createEntry', ['admin', [
            'name'     => 'users',
            'route'    => 'ekyna_admin_user_admin_list',
            'label'    => 'ekyna_admin.user.label.plural',
            'resource' => 'ekyna_admin_user',
            'position' => 1,
        ]]);
        $pool->addMethodCall('createEntry', ['admin', [
            'name'     => 'groups',
            'route'    => 'ekyna_admin_group_admin_list',
            'label'    => 'ekyna_admin.group.label.plural',
            'resource' => 'ekyna_admin_group',
            'position' => 2,
        ]]);
    }
}
