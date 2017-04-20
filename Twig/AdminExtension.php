<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Service\Pin\PinHelper;
use Ekyna\Bundle\AdminBundle\Service\Renderer\AdminRenderer;
use Ekyna\Bundle\AdminBundle\Service\Renderer\DashboardRenderer;
use Ekyna\Bundle\AdminBundle\Service\Renderer\OauthRenderer;
use Ekyna\Bundle\AdminBundle\Service\Search\SearchHelper;
use Ekyna\Bundle\AdminBundle\Table\ResourceTableHelper;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            /** @see AdminRenderer::getNavbarConfig() */
            new TwigFunction(
                'admin_navbar_config',
                [AdminRenderer::class, 'getNavbarConfig']
            ),
            /** @see AdminRenderer::renderStylesheets() */
            new TwigFunction(
                'admin_stylesheets',
                [AdminRenderer::class, 'renderStylesheets'],
                ['is_safe' => ['html']]
            ),
            /** @see AdminRenderer::renderResourceButton() */
            new TwigFunction(
                'admin_resource_btn',
                [AdminRenderer::class, 'renderResourceButton'],
                ['is_safe' => ['html']]
            ),
            /** @see AdminRenderer::hasResourceAccess() */
            new TwigFunction(
                'admin_resource_access',
                [AdminRenderer::class, 'hasResourceAccess']
            ),
            /** @see AdminRenderer::generateResourcePath() */
            new TwigFunction(
                'admin_resource_path',
                [AdminRenderer::class, 'generateResourcePath']
            ),
            /** @see AdminRenderer::generateSummaryPath() */
            new TwigFunction(
                'admin_resource_summary',
                [AdminRenderer::class, 'generateSummaryPath'],
                ['is_safe' => ['html']]
            ),
            /** @see ResourceTableHelper::createResourceTableView() */
            new TwigFunction(
                'admin_resource_table',
                [ResourceTableHelper::class, 'createResourceTableView'],
                ['is_safe' => ['html']]
            ),
            /** @see AdminRenderer::renderFrontHelper() */
            new TwigFunction(
                'admin_front_helper',
                [AdminRenderer::class, 'renderFrontHelper'],
                ['is_safe' => ['html']]
            ),
            /** @see AdminRenderer::renderResourcePin() */
            new TwigFunction(
                'admin_resource_pin',
                [AdminRenderer::class, 'renderResourcePin'],
                ['is_safe' => ['html']]
            ),
            /** @see PinHelper::getUserPins() */
            new TwigFunction(
                'admin_user_pins',
                [PinHelper::class, 'getUserPins']
            ),
            /** @see SearchHelper::render() */
            new TwigFunction(
                'admin_search_bar',
                [SearchHelper::class, 'render'],
                ['is_safe' => ['html']]
            ),
            /** @see DashboardRenderer::renderWidget() */
            new TwigFunction(
                'admin_dashboard_widget',
                [DashboardRenderer::class, 'renderWidget'],
                ['is_safe' => ['html']]
            ),
            /** @see OauthRenderer::getOAuthConnectButtons() */
            new TwigFunction(
                'oauth_owners',
                [OauthRenderer::class, 'getOAuthConnectButtons']
            ),
        ];
    }
}
