<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class BooleanType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class BooleanType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'value'       => $value,
            'color'       => $options['color'],
            'null_label'  => $options['null_label'],
            'null_class'  => $options['null_class'],
            'true_class'  => $options['true_class'],
            'false_class' => $options['false_class'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'color'       => false,
                'null_label'  => 'ekyna_core.value.undefined',
                'null_class'  => 'label label-default',
                'true_class'  => 'label label-success',
                'false_class' => 'label label-danger',
            ]);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'boolean';
    }
}