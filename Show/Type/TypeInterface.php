<?php

namespace Ekyna\Bundle\AdminBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\View;

/**
 * Interface TypeInterface
 * @package Ekyna\Bundle\AdminBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TypeInterface
{
    /**
     * Resolve the options.
     *
     * @param array $options The options to resolve.
     *
     * @return array The resolved options.
     */
    public function resolveOptions(array $options = []);

    /**
     * Builds the view.
     *
     * @param View  $view    The view to build
     * @param mixed $value   The value to render
     * @param array $options The resolved options
     */
    public function build(View $view, $value, array $options = []);

    /**
     * Returns the row prefix.
     *
     * @return string
     */
    public function getRowPrefix();

    /**
     * Returns the widget prefix.
     *
     * @return string
     */
    public function getWidgetPrefix();
}