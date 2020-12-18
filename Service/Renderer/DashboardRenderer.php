<?php

namespace Ekyna\Bundle\AdminBundle\Service\Renderer;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Twig\Environment;
use Twig\Extension\RuntimeExtensionInterface;

/**
 * Class Renderer
 * @package Ekyna\Bundle\AdminBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardRenderer implements RuntimeExtensionInterface
{
    /**
     * @var Environment
     */
    private $twig;


    /**
     * Constructor.
     *
     * @param Environment $twig
     */
    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Renders the widget.
     *
     * @param WidgetInterface   $widget
     *
     * @return string
     */
    public function renderWidget(WidgetInterface $widget)
    {
        return $widget->getType()->render($widget, $this->twig);
    }
}
