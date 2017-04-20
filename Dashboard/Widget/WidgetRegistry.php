<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;
use Psr\Container\ContainerInterface;
use UnexpectedValueException;

/**
 * Class WidgetRegistry
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WidgetRegistry
{
    private ContainerInterface $locator;

    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * Returns whether a widget type is registered for the given name.
     */
    public function has(string $name): bool
    {
        return $this->locator->has($name);
    }

    /**
     * Returns the widget type by its name.
     */
    public function get(string $name): WidgetTypeInterface
    {
        if (!$this->has($name)) {
            throw new UnexpectedValueException("No widget type registered under the name '$name'.");
        }

        return $this->locator->get($name);
    }
}
