<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;

/**
 * Interface WidgetInterface
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
interface WidgetInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string;

    /**
     * Returns the type.
     *
     * @return WidgetTypeInterface
     */
    public function getType(): WidgetTypeInterface;

    /**
     * Sets the options.
     *
     * @param array $options
     *
     * @return WidgetInterface
     */
    public function setOptions(array $options): WidgetInterface;

    /**
     * Returns the value for the given option name.
     *
     * @param string $name
     * @param mixed  $default
     *
     * @return mixed
     */
    public function getOption(string $name, $default = null);

    /**
     * Returns the options.
     *
     * @return array
     */
    public function getOptions(): array;
}
