<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type\WidgetTypeInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class Factory
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Factory
{
    /**
     * @var Registry
     */
    protected $registry;


    /**
     * Constructor.
     *
     * @param Registry $registry
     */
    public function __construct(Registry $registry)
    {
        $this->registry = $registry;
    }

    /**
     * Creates the widget.
     *
     * @param string                     $name
     * @param string|WidgetTypeInterface $type
     * @param array                      $options
     * @return Widget
     */
    public function create($name, $type, array $options)
    {
        if (is_string($type)) {
            $type = $this->registry->getType($type);
        }
        if (!$type instanceof WidgetTypeInterface) {
            throw new \InvalidArgumentException('Unexpected widget type.');
        }

        $options['name'] = $name;

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $widget = new Widget($name, $type);
        $type->buildWidget($widget, $options);

        return $widget;
    }
}
