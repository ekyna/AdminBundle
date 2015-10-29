<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;

/**
 * Interface WidgetInterface
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface WidgetInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Returns the type.
     *
     * @return WidgetTypeInterface
     */
    public function getType();

    /**
     * Sets the options.
     *
     * @param array $options
     * @return WidgetInterface
     */
    public function setOptions($options);

    /**
     * Returns the options.
     *
     * @return array
     */
    public function getOptions();
}
