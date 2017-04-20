<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type;

use Ekyna\Bundle\AdminBundle\Dashboard\Widget\WidgetInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use Symfony\Contracts\Translation\TranslatableInterface;

use function array_merge;
use function array_unique;
use function implode;
use function in_array;

/**
 * Class AbstractWidgetType
 * @package Ekyna\Bundle\AdminBundle\Dashboard\Widget\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractWidgetType implements WidgetTypeInterface
{
    public function buildWidget(WidgetInterface $widget, array $options): void
    {
        $attr = ['name' => $options['name']];
        $classes = [];

        foreach (['xs', 'sm', 'md', 'lg'] as $sizing) {
            $size = $options['col_' . $sizing];
            if (0 < $size && $size < 12) {
                $classes[] = 'col-' . $sizing . '-' . $size;
            }
        }

        if (empty($classes)) {
            $classes[] = 'col-md-12';
        }

        if (!empty($options['class'])) {
            $classes[] = $options['class'];
        }

        $attr['class'] = implode(' ', array_unique($classes));

        $widget->setOptions(array_merge($options, ['attr' => $attr]));
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        /**
         * Validates the column min size.
         *
         * @param int $value
         *
         * @return bool
         */
        $minSizeValidator = function (int $value): bool {
            return 0 < $value && $value < 13;
        };

        /**
         * Normalizes the column size.
         *
         * @param int $min
         * @param int $value
         *
         * @return int
         */
        $sizeNormalizer = function (int $min, int $value): int {
            return $min > $value ? $min : $value;
        };

        $resolver
            ->setDefaults([
                'name'         => null,
                'title'        => null,
                'trans_domain' => null,
                'frame'        => true,
                'theme'        => 'default',
                'class'        => null,
                'col_xs_min'   => 12,
                'col_sm_min'   => 12,
                'col_md_min'   => 6,
                'col_lg_min'   => 6,
                'col_xs'       => 12,
                'col_sm'       => 12,
                'col_md'       => 12,
                'col_lg'       => 12,
                'position'     => 0,
                'css_path'     => null,
                'js_path'      => null,
            ])
            ->setRequired(['name', 'title'])
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('title', ['null', 'string', TranslatableInterface::class])
            ->setAllowedTypes('trans_domain', ['null', 'string'])
            ->setAllowedTypes('frame', 'bool')
            ->setAllowedTypes('theme', ['null', 'string'])
            ->setAllowedTypes('class', ['null', 'string'])
            ->setAllowedTypes('col_xs_min', 'int')
            ->setAllowedTypes('col_sm_min', 'int')
            ->setAllowedTypes('col_md_min', 'int')
            ->setAllowedTypes('col_lg_min', 'int')
            ->setAllowedTypes('col_xs', 'int')
            ->setAllowedTypes('col_sm', 'int')
            ->setAllowedTypes('col_md', 'int')
            ->setAllowedTypes('col_lg', 'int')
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('css_path', ['null', 'string'])
            ->setAllowedTypes('js_path', ['null', 'string'])
            ->setAllowedValues('theme', function (string $value): bool {
                return null === $value
                    || in_array($value, [
                        'default',
                        'primary',
                        'success',
                        'info',
                        'warning',
                        'danger',
                    ]);
            })
            ->setAllowedValues('col_xs_min', $minSizeValidator)
            ->setAllowedValues('col_sm_min', $minSizeValidator)
            ->setAllowedValues('col_md_min', $minSizeValidator)
            ->setAllowedValues('col_lg_min', $minSizeValidator)
            ->setNormalizer('col_xs', function (Options $options, $value) use ($sizeNormalizer): int {
                return $sizeNormalizer($options['col_xs_min'], $value);
            })
            ->setNormalizer('col_sm', function (Options $options, $value) use ($sizeNormalizer): int {
                return $sizeNormalizer($options['col_sm_min'], $value);
            })
            ->setNormalizer('col_md', function (Options $options, $value) use ($sizeNormalizer): int {
                return $sizeNormalizer($options['col_md_min'], $value);
            })
            ->setNormalizer('col_lg', function (Options $options, $value) use ($sizeNormalizer): int {
                return $sizeNormalizer($options['col_lg_min'], $value);
            })
            ->setNormalizer(
                'position',
                function (Options $options, $value) use ($sizeNormalizer): int {
                    return 0 > $value ? 0 : $value;
                }
            );
    }
}
