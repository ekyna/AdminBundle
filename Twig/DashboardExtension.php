<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;

/**
 * Class DashboardExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardExtension extends \Twig_Extension
{
    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'render_dashboard_widget',
                [$this, 'renderDashboardWidget'],
                ['is_safe' => ['html'], 'needs_environment' => true]
            ),
        ];
    }

    /**
     * Renders the widget.
     *
     * @param \Twig_Environment $env
     * @param WidgetInterface   $widget
     *
     * @return string
     */
    public function renderDashboardWidget(\Twig_Environment $env, WidgetInterface $widget)
    {
        return $widget->getType()->render($widget, $env);
    }
}
