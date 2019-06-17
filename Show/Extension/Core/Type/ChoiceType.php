<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ChoiceType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ChoiceType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $choices = $options['choices'];

        if (is_string($choices) && class_exists($choices)) {
            $choices = call_user_func($choices . '::getChoices');
        } elseif (is_callable($choices)) {
            $choices = call_user_func($choices);
        } elseif (!is_array($choices)) {
            throw new InvalidArgumentException("Unexpected choices.");
        }

        if ($options['multiple']) {
            if (!is_array($value)) {
                throw new InvalidArgumentException("Unexpected array value.");
            }
        } else {
            $value = [$value];
        }

        $values = [];
        foreach ($value as $val) {
            if (false !== $choice = array_search($val, $choices, true)) {
                $values[] = (string)$choice;
            }
        }

        if (empty($values)) {
            $values[] = $options['empty_label'];
        }

        $view->vars = array_replace($view->vars, [
            'multiple'     => $options['multiple'],
            'trans_domain' => $options['trans_domain'],
            'trans_params' => $options['trans_params'],
            'values'       => $values,
        ]);
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setRequired('choices')
            ->setDefaults([
                'multiple'     => false,
                'trans_params' => [],
                'empty_label'  => 'ekyna_core.value.undefined',
            ])
            ->setAllowedTypes('multiple', 'bool')
            ->setAllowedTypes('choices', ['array', 'callable', 'string'])
            ->setAllowedTypes('trans_params', 'array')
            ->setAllowedTypes('empty_label', 'string');
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'choice';
    }
}