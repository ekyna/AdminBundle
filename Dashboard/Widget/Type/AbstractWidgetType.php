<?php

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractWidgetType
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractWidgetType implements WidgetTypeInterface
{
    /**
     * {@inheritdoc}
     */
    public function buildWidget(WidgetInterface $widget, array $options)
    {
        $attr = ['name' => $options['name']];
        $classes = [];
        foreach (array('xs', 'sm', 'md', 'lg') as $sizing) {
            $size = $options['col_' . $sizing];
            if (0 < $size && $size < 12) {
                $classes[] = 'col-' . $sizing . '-' . $options['col_' . $sizing];
            }
        }
        if (empty($classes)) {
            $classes[] = 'col-md-12';
        }
        $attr['class'] = implode(' ', $classes);

        $widget->setOptions(array_merge($options, array('attr' => $attr)));
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        /**
         * Validates the column min size.
         *
         * @param int $value
         *
         * @return bool
         */
        $minSizeValidator = function ($value) {
            return 0 < $value && $value < 13;
        };

        /**
         * Normalizes the column size.
         *
         * @param $min
         * @param $value
         *
         * @return mixed
         */
        $sizeNormalizer = function ($min, $value) {
            return $min > $value ? $min : $value;
        };

        /** @noinspection PhpUnusedParameterInspection */
        $resolver
            ->setDefaults(array(
                'name'       => null,
                'title'      => null,
                'theme'      => 'default',
                'col_xs_min' => 12,
                'col_sm_min' => 12,
                'col_md_min' => 6,
                'col_lg_min' => 6,
                'col_xs'     => 12,
                'col_sm'     => 12,
                'col_md'     => 12,
                'col_lg'     => 12,
                'position'   => 0,
                'css_path'   => null,
                'js_path'    => null,
            ))
            ->setRequired(array('name', 'title'))

            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('title', array('null', 'string'))
            ->setAllowedTypes('theme', array('null', 'string'))
            ->setAllowedTypes('col_xs_min', 'int')
            ->setAllowedTypes('col_sm_min', 'int')
            ->setAllowedTypes('col_md_min', 'int')
            ->setAllowedTypes('col_lg_min', 'int')
            ->setAllowedTypes('col_xs', 'int')
            ->setAllowedTypes('col_sm', 'int')
            ->setAllowedTypes('col_md', 'int')
            ->setAllowedTypes('col_lg', 'int')
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('css_path', array('null', 'string'))
            ->setAllowedTypes('js_path', array('null', 'string'))
            ->setAllowedValues('theme', function ($value) {
                return null === $value || in_array($value, array(
                    'default',
                    'primary',
                    'success',
                    'info',
                    'warning',
                    'danger',
                ));
            })
            ->setAllowedValues('col_xs_min', $minSizeValidator)
            ->setAllowedValues('col_sm_min', $minSizeValidator)
            ->setAllowedValues('col_md_min', $minSizeValidator)
            ->setAllowedValues('col_lg_min', $minSizeValidator)

            ->setNormalizer('col_xs', function (Options $options, $value) use ($sizeNormalizer) {
                return $sizeNormalizer($options['col_xs_min'], $value);
            })
            ->setNormalizer('col_sm', function (Options $options, $value) use ($sizeNormalizer) {
                return $sizeNormalizer($options['col_sm_min'], $value);
            })
            ->setNormalizer('col_md', function (Options $options, $value) use ($sizeNormalizer) {
                return $sizeNormalizer($options['col_md_min'], $value);
            })
            ->setNormalizer('col_lg', function (Options $options, $value) use ($sizeNormalizer) {
                return $sizeNormalizer($options['col_lg_min'], $value);
            })
            ->setNormalizer('position', function (Options $options, $value) use ($sizeNormalizer) {
                return 0 > $value ? 0 : $value;
            })
        ;
    }
}
