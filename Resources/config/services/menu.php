<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Bundle\AdminBundle\Service\Menu\MenuPool;
use Knp\Menu\MenuItem;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Menu pool
        ->set('ekyna_admin.menu.pool', MenuPool::class)

        // Menu builder
        ->set('ekyna_admin.menu.builder', MenuBuilder::class)
            ->args([
                service('knp_menu.factory'),
                service('ekyna_admin.menu.pool'),
                service('ekyna_resource.helper'),
            ])

        // Side menu
        ->set('ekyna_admin.menu.side', MenuItem::class)
            ->factory([
                service('ekyna_admin.menu.builder'),
                'createSideMenu'
            ])
            ->args([
                service('request_stack'),
            ])
            ->tag('knp_menu.menu', ['alias' => 'ekyna_admin.side'])

        // Breadcrumb
        ->set('ekyna_admin.menu.breadcrumb', MenuItem::class)
            ->factory([
                service('ekyna_admin.menu.builder'),
                'createBreadcrumb'
            ])
            ->args([
                service('request_stack'),
            ])
            ->tag('knp_menu.menu', ['alias' => 'ekyna_admin.breadcrumb'])
    ;
};
