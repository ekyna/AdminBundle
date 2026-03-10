<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Service\Security\SecurityUtil;
use Ekyna\Bundle\AdminBundle\Show\ShowRenderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ShowExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShowExtension extends AbstractExtension
{
    public function getFunctions(): array
    {
        return [
            new TwigFunction(
                'show_row',
                [ShowRenderer::class, 'renderRow'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'show_widget',
                [ShowRenderer::class, 'renderWidget'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'password_generation_allowed',
                [SecurityUtil::class, 'isPasswordGenerationAllowed']
            ),
        ];
    }
}
