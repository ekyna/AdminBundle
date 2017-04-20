<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function array_replace;

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
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $view->vars = array_replace($view->vars, [
            'value'       => $value,
            'toggle_path' => $options['toggle_path'],
            'color'       => $options['color'],
            'null_label'  => $options['null_label'],
            'null_class'  => $options['null_class'],
            'true_label'  => $options['true_label'],
            'true_class'  => $options['true_class'],
            'false_label' => $options['false_label'],
            'false_class' => $options['false_class'],
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'toggle_path' => null,
                'color'       => false,
                'null_label'  => 'value.undefined',
                'null_class'  => 'label label-default',
                'true_label'  => 'value.yes',
                'true_class'  => 'label label-success',
                'false_label' => 'value.no',
                'false_class' => 'label label-danger',
                'trans_domain' => 'EkynaUi',
            ])
            ->setNormalizer('color', function (Options $options, $value) {
                if (!empty($options['toggle_path'])) {
                    return true;
                }

                return $value;
            });
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'boolean';
    }
}
