<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Dashboard\Dashboard;
use Ekyna\Bundle\AdminBundle\Dashboard\DashboardFactory;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\ShortcutsWidgetType;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetFactory;
use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetRegistry;
use Ekyna\Bundle\AdminBundle\Service\Renderer\DashboardRenderer;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Widget registry
        ->set('ekyna_admin.dashboard.widget_registry', WidgetRegistry::class)
            ->args([
                // Replaced by compiler pass
                abstract_arg('The widgets types services locator'),
            ])

        // Widget factory
        ->set('ekyna_admin.dashboard.widget_factory', WidgetFactory::class)
            ->args([
                service('ekyna_admin.dashboard.widget_registry'),
            ])

        // Dashboard factory
        ->set('ekyna_admin.dashboard.dashboard_factory', DashboardFactory::class)
            ->args([
                service('ekyna_admin.dashboard.widget_factory'),
            ])

        // Shortcuts widget type
        ->set('ekyna_admin.dashboard.widget.shortcuts', ShortcutsWidgetType::class)
            ->args([
                service('ekyna_admin.menu.pool'),
                service('ekyna_resource.helper'),
            ])
            ->tag('ekyna_admin.dashboard_widget')

        // Dashboard
        ->set('ekyna_admin.dashboard', Dashboard::class)
            ->factory([service('ekyna_admin.dashboard.dashboard_factory'), 'create'])
            ->args([
                // Replaced by DI extension
                abstract_arg('The dashboard configuration'),
            ])

        // Dashboard renderer
        ->set('ekyna_admin.dashboard.renderer', DashboardRenderer::class)
            ->args([
                service('twig')
            ])
            ->tag('twig.runtime')
    ;
};
