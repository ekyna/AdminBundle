<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use InvalidArgumentException;

use function array_key_exists;
use function in_array;
use function sprintf;
use function usort;

/**
 * Class Dashboard
 * @package Ekyna\Bundle\AdminBundle\Dashboard
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class Dashboard
{
    /** @var array<WidgetInterface> */
    protected array $widgets = [];
    /** @var array<WidgetInterface>|null */
    protected ?array $sortedWidgets = null;

    /**
     * Returns whether the dashboard has the widget or not.
     */
    public function hasWidget(WidgetInterface|string $nameOrWidget): bool
    {
        $name = $nameOrWidget instanceof WidgetInterface ? $nameOrWidget->getName() : $nameOrWidget;

        return array_key_exists($name, $this->widgets);
    }

    /**
     * Adds the widget.
     */
    public function addWidget(WidgetInterface $widget): Dashboard
    {
        if ($this->hasWidget($widget)) {
            throw new InvalidArgumentException(sprintf('Widget "%s" is already registered.', $widget->getName()));
        }

        $this->widgets[$widget->getName()] = $widget;
        $this->sortedWidgets = null;

        return $this;
    }

    /**
     * Returns the widgets.
     *
     * @return array<WidgetInterface>
     */
    public function getWidgets(): array
    {
        if (null !== $this->sortedWidgets) {
            return $this->sortedWidgets;
        }

        $widgets = $this->widgets;

        usort($widgets, function (WidgetInterface $a, WidgetInterface $b) {
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
     * Returns the stylesheets files paths.
     *
     * @return array<string>
     */
    public function getStylesheets(): array
    {
        $paths = [];

        foreach ($this->widgets as $widget) {
            if (empty($path = $widget->getOption('css_path'))) {
                continue;
            }

            if (!in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }

    /**
     * Returns the javascript files paths.
     *
     * @return array<string>
     */
    public function getJavascripts(): array
    {
        $paths = [];

        foreach ($this->widgets as $widget) {
            if (empty($path = $widget->getOption('js_path'))) {
                continue;
            }

            if (!in_array($path, $paths, true)) {
                $paths[] = $path;
            }
        }

        return $paths;
    }
}
