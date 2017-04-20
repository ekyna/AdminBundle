<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class WidgetFactory
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class WidgetFactory
{
    protected WidgetRegistry $registry;

    public function __construct(WidgetRegistry $registry)
    {
        $this->registry = $registry;
    }

    public function create(string $name, string $type, array $options): Widget
    {
        $type = $this->registry->get($type);

        $options['name'] = $name;

        $resolver = new OptionsResolver();
        $type->configureOptions($resolver);
        $options = $resolver->resolve($options);

        $widget = new Widget($name, $type);
        $type->buildWidget($widget, $options);

        return $widget;
    }
}
