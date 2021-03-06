<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Interface WidgetTypeInterface
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface WidgetTypeInterface
{
    /**
     * Builds the widget.
     *
     * @param WidgetInterface $widget
     * @param array           $options
     */
    public function buildWidget(WidgetInterface $widget, array $options);

    /**
     * Renders the widget.
     *
     * @param WidgetInterface   $widget
     * @param \Twig_Environment $twig
     * @return string
     */
    public function render(WidgetInterface $widget, \Twig_Environment $twig);

    /**
     * Configures the options.
     *
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver);

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();
}
