<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;
use function is_array;

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
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        if ($value instanceof Collection) {
            $value = $value->toArray();
        } elseif (null === $value) {
            $value = [];
        } elseif (!is_array($value)) {
            $value = [$value];
        }

        $view->vars = array_replace($view->vars, [
            'value'                => $value,
            'property'             => $options['property'],
            'route'                => $options['route'],
            'route_parameters'     => $options['route_parameters'],
            'route_parameters_map' => $options['route_parameters_map'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'property'             => null,
                'action'               => null,
                'route'                => null,
                'route_parameters'     => [],
                'route_parameters_map' => [],
            ])
            ->setAllowedTypes('property', ['null', 'string'])
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('route_parameters', 'array')
            ->setAllowedTypes('route_parameters_map', 'array');
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'entity';
    }
}
