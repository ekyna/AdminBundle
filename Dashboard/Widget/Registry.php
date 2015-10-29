<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;

/**
 * Class Registry
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Registry
{
    /**
     * @var array
     */
    private $types;


    /**
     * Constructor.
     *
     * @param array $types
     */
    public function __construct(array $types = array())
    {
        $this->types = $types;
    }

    /**
     * Returns whether the widget type is registered or not.
     *
     * @param $nameOrType
     * @return bool
     */
    public function hasType($nameOrType)
    {
        $name = $nameOrType instanceof WidgetTypeInterface ? $nameOrType->getName() : $nameOrType;

        return array_key_exists($name, $this->types);
    }

    /**
     * Adds the widget type.
     *
     * @param WidgetTypeInterface $widget
     * @throws \InvalidArgumentException
     */
    public function addType(WidgetTypeInterface $widget)
    {
        if (!$this->hasType($widget)) {
            $this->types[$widget->getName()] = $widget;
        } else {
            throw new \InvalidArgumentException(sprintf('Widget type "%s" is already registered.', $widget->getName()));
        }
    }

    /**
     * Returns the widget type by name.
     *
     * @param string $name
     * @return WidgetTypeInterface
     * @throws \InvalidArgumentException
     */
    public function getType($name)
    {
        if ($this->hasType($name)) {
            return $this->types[$name];
        } else {
            throw new \InvalidArgumentException(sprintf('Widget type "%s" not found.', $name));
        }
    }
}
