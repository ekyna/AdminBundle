<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard;

/**
 * Class DashboardFactory
 * @package Ekyna\Bundle\AdminBundle\Dashboard
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardFactory
{
    protected Widget\WidgetFactory $widgetFactory;


    /**
     * Constructor.
     *
     * @param Widget\WidgetFactory $widgetFactory
     */
    public function __construct(Widget\WidgetFactory $widgetFactory)
    {
        $this->widgetFactory = $widgetFactory;
    }

    /**
     * Creates the dashboard.
     *
     * @param array $config
     *
     * @return Dashboard
     */
    public function create(array $config): Dashboard
    {
        $dashboard = new Dashboard();

        foreach ($config as $name => $cfg) {
            $widget = $this->widgetFactory->create($name, $cfg['type'], $cfg['options']);
            $dashboard->addWidget($widget);
        }

        return $dashboard;
    }
}
