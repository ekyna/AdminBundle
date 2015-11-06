<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;

/**
 * Class Dashboard
 * @package Ekyna\Bundle\AdminBundle\Dashboard
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Dashboard
{
    /**
     * @var WidgetInterface[]
     */
    protected $widgets;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->widgets = [];
    }

    /**
     * Returns whether the dashboard has the widget or not.
     *
     * @param string|WidgetInterface $nameOrWidget
     * @return bool
     */
    public function hasWidget($nameOrWidget)
    {
        $name = $nameOrWidget instanceof WidgetInterface ? $nameOrWidget->getName() : $nameOrWidget;

        return array_key_exists($name, $this->widgets);
    }

    /**
     * Adds the widget.
     *
     * @param WidgetInterface $widget
     * @return Dashboard
     */
    public function addWidget(WidgetInterface $widget)
    {
        if ($this->hasWidget($widget)) {
            throw new \InvalidArgumentException(sprintf('Widget "%s" is already registered.', $widget->getName()));
        }

        $this->widgets[$widget->getName()] = $widget;

        return $this;
    }

    /**
     * Returns the widgets.
     *
     * @return array|Widget\WidgetInterface[]
     */
    public function getWidgets()
    {
        return $this->widgets;
    }
}
