<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TinyMceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TinyMceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'height'       => $options['height'],
            'route'        => $options['route'],
            'route_params' => $options['route_params'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'height'       => 250,
                'route'        => null,
                'route_params' => [],
            ])
            ->setAllowedTypes('height', 'int')
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('route_params', 'array');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'tinymce';
    }
}
