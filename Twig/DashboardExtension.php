<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;

/**
 * Class DashboardExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardExtension extends \Twig_Extension
{
    private $twig;


    /**
     * {@inheritdoc}
     */
    public function initRuntime(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('render_dashboard_widget', array($this, 'renderDashboardWidget'), array('is_safe' => array('html'))),
        );
    }

    /**
     * Renders the widget.
     *
     * @param WidgetInterface $widget
     * @return string
     */
    public function renderDashboardWidget(WidgetInterface $widget)
    {
        return $widget->getType()->render($widget, $this->twig);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_dashboard_extension';
    }
}
