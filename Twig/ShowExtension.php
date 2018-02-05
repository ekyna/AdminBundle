<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Show\RendererInterface;

/**
 * Class ShowExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ShowExtension extends \Twig_Extension
{
    /**
     * @var RendererInterface
     */
    protected $renderer;


    /**
     * Constructor
     *
     * @param RendererInterface $renderer
     */
    public function __construct(RendererInterface $renderer)
    {
        $this->renderer = $renderer;
    }

    /**
     * {@inheritdoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'show_row',
                [$this->renderer, 'renderRow'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'show_widget',
                [$this->renderer, 'renderWidget'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_show_extension';
    }
}
