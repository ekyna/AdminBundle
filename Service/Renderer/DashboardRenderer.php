<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Renderer;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Twig\Environment;

/**
 * Class Renderer
 * @package Ekyna\Bundle\AdminBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DashboardRenderer
{
    private Environment $twig;

    public function __construct(Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * Renders the widget.
     */
    public function renderWidget(WidgetInterface $widget): string
    {
        return $widget->getType()->render($widget, $this->twig);
    }
}
