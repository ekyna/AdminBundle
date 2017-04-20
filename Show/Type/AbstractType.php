<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_replace;

/**
 * Class AbstractType
 * @package Ekyna\Bundle\AdminBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractType implements TypeInterface
{
    private ?OptionsResolver $optionResolver = null;


    /**
     * @inheritDoc
     */
    public function resolveOptions(array $options = []): array
    {
        return $this->getOptionResolver()->resolve($options);
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        $attr = $options['attr'];
        foreach (['id', 'class'] as $key) {
            if (isset($options[$key])) {
                $attr[$key] = $options[$key];
            }
        }

        $view->vars = array_replace($view->vars, [
            'label_col'          => $options['label_col'],
            'widget_col'         => $options['widget_col'],
            'label'              => $options['label'],
            'label_trans_domain' => $options['label_trans_domain'],
            'trans_domain'       => $options['trans_domain'],
            'locale'             => $options['locale'],
            'attr'               => $attr,
            'value'              => $value,
            'row_prefix'         => $options['row_prefix'] ?: $this->getRowPrefix() ?: 'default',
            'widget_prefix'      => $options['widget_prefix'] ?: $this->getWidgetPrefix() ?: static::getName(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getRowPrefix(): ?string
    {
        return null;
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix(): ?string
    {
        return null;
    }

    /**
     * Configures the type's options.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver): void
    {
    }

    /**
     * Returns the option resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionResolver(): OptionsResolver
    {
        if (null !== $this->optionResolver) {
            return $this->optionResolver;
        }

        $this->optionResolver = new OptionsResolver();
        $this->optionResolver
            ->setDefaults([
                'id'                 => null,
                'class'              => null,
                'label'              => null,
                'label_trans_domain' => null,
                'trans_domain'       => false,
                'locale'             => null,
                'label_col'          => 2,
                'widget_col'         => 10,
                'row_prefix'         => null,
                'widget_prefix'      => null,
                'attr'               => [],
            ])
            ->setAllowedTypes('id', ['null', 'string'])
            ->setAllowedTypes('label', ['null', 'string', TranslatableInterface::class])
            ->setAllowedTypes('label_trans_domain', ['null', 'bool', 'string'])
            ->setAllowedTypes('trans_domain', ['null', 'bool', 'string'])
            ->setAllowedTypes('locale', ['null', 'string'])
            ->setAllowedTypes('class', ['null', 'string'])
            ->setAllowedTypes('label_col', 'int')
            ->setAllowedTypes('widget_col', 'int')
            ->setAllowedTypes('row_prefix', ['null', 'string'])
            ->setAllowedTypes('widget_prefix', ['null', 'string'])
            ->setAllowedTypes('attr', 'array')
            ->setNormalizer(
                'label_trans_domain',
                function (Options $options, $value) {
                    if (true === $value) {
                        return null;
                    }

                    return $value;
                }
            )
            ->setNormalizer(
                'trans_domain',
                function (Options $options, $value) {
                    if (true === $value) {
                        return null;
                    }

                    return $value;
                }
            )
            ->setNormalizer('widget_col', function (Options $options, $value) {
                if (12 != $options['label_col'] + $value) {
                    $value = 12 - $options['label_col'];
                }

                return $value;
            });

        $this->configureOptions($this->optionResolver);

        return $this->optionResolver;
    }
}
