<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif(null === $value) {
            $value = [];
        } elseif(!is_array($value)) {
            $value = [$value];
        }

        $view->vars = array_replace($view->vars, [
            'value'                => $value,
            'route'                => $options['route'],
            'field'                => $options['field'],
            'route_parameters'     => $options['route_parameters'],
            'route_parameters_map' => $options['route_parameters_map'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'route'                => null,
                'field'                => null, // TODO rename to property_path
                'route_parameters'     => [],
                'route_parameters_map' => [],
            ])
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('field', ['null', 'string'])
            ->setAllowedTypes('route_parameters', 'array')
            ->setAllowedTypes('route_parameters_map', 'array');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'entity';
    }
}