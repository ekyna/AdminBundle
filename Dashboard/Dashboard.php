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
    protected $widgets = [];

    /**
     * @var WidgetInterface[]
     */
    protected $sortedWidgets = null;


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
        $this->sortedWidgets = null;

        return $this;
    }

    /**
     * Returns the widgets.
     *
     * @return array|Widget\WidgetInterface[]
     */
    public function getWidgets()
    {
        if (null !== $this->sortedWidgets) {
            return $this->sortedWidgets;
        }

        $widgets = $this->widgets;

        usort($widgets, function(WidgetInterface $a, WidgetInterface $b) {
            $aPos = $a->getOption('position');
            $bPos = $b->getOption('position');

            if ($aPos === $bPos) {
                return 0;
            }

            return $aPos > $bPos ? -1 : 1;
        });

        return $this->sortedWidgets = $widgets;
    }

    /**
     * Returns the stylesheets paths.
     *
     * @return array
     */
    public function getStylesheets()
    {
        $stylesheets = [];

        foreach ($this->widgets as $widget) {
            if (!empty($path = $widget->getOption('css_path'))) {
                if (!in_array($path, $stylesheets)) {
                    $stylesheets[] = $path;
                }
            }
        }

        return $stylesheets;
    }

    /**
     * Returns the javascripts paths.
     *
     * @return array
     */
    public function getJavascripts()
    {
        $javascripts = [];

        foreach ($this->widgets as $widget) {
            if (!empty($path = $widget->getOption('js_path'))) {
                if (!in_array($path, $javascripts)) {
                    $javascripts[] = $path;
                }
            }
        }

        return $javascripts;
    }
}
