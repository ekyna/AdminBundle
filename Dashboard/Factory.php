<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard;

/**
 * Class Factory
 * @package Ekyna\Bundle\AdminBundle\Dashboard
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Factory
{
    /**
     * @var Widget\Factory
     */
    protected $widgetFactory;


    /**
     * Constructor.
     * @param Widget\Factory $widgetFactory
     */
    public function __construct(Widget\Factory $widgetFactory)
    {
        $this->widgetFactory = $widgetFactory;
    }

    /**
     * Creates the dashboard.
     *
     * @param array $config
     * @return Dashboard
     */
    public function create(array $config)
    {
        $dashboard = new Dashboard();

        foreach ($config as $name => $cfg) {
            $widget = $this->widgetFactory->create($name, $cfg['type'], $cfg['options']);
            $dashboard->addWidget($widget);
        }

        return $dashboard;
    }
}
