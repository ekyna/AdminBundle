<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Helper\PinHelper;
use Ekyna\Bundle\AdminBundle\Service\Renderer\AdminRenderer;
use Ekyna\Bundle\AdminBundle\Service\Renderer\DashboardRenderer;
use Ekyna\Bundle\AdminBundle\Service\Search\SearchHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends AbstractExtension
{
    /**
     * @inheritDoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'admin_navbar_config',
                [AdminRenderer::class, 'getNavbarConfig']
            ),
            new TwigFunction(
                'admin_stylesheets',
                [AdminRenderer::class, 'renderStylesheets'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_resource_btn',
                [AdminRenderer::class, 'renderResourceButton'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_resource_access',
                [AdminRenderer::class, 'hasResourceAccess']
            ),
            new TwigFunction(
                'admin_resource_path',
                [AdminRenderer::class, 'generateResourcePath']
            ),
            new TwigFunction(
                'admin_resource_summary',
                [AdminRenderer::class, 'generateSummaryPath'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_user_pins',
                [PinHelper::class, 'getUserPins']
            ),
            new TwigFunction(
                'admin_resource_pin',
                [AdminRenderer::class, 'renderResourcePin'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_front_helper',
                [AdminRenderer::class, 'renderFrontHelper'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_search_bar',
                [SearchHelper::class, 'render'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'admin_dashboard_widget',
                [DashboardRenderer::class, 'renderWidget'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
