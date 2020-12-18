<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Show\Renderer;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Class ShowExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShowExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function getFunctions()
    {
        return [
            new TwigFunction(
                'show_row',
                [Renderer::class, 'renderRow'],
                ['is_safe' => ['html']]
            ),
            new TwigFunction(
                'show_widget',
                [Renderer::class, 'renderWidget'],
                ['is_safe' => ['html']]
            ),
        ];
    }
}
