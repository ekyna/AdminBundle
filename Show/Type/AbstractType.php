<?php

namespace Ekyna\Bundle\AdminBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AbstractType
 * @package Ekyna\Bundle\AdminBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractType implements TypeInterface
{
    /**
     * @var OptionsResolver
     */
    private $optionResolver;


    /**
     * @inheritDoc
     */
    public function resolveOptions(array $options = [])
    {
        return $this->getOptionResolver()->resolve($options);
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        $attr = $options['attr'];
        foreach (['id', 'class'] as $key) {
            if (isset($options[$key])) {
                $attr[$key] = $options[$key];
            }
        }

        $view->vars = array_replace($view->vars, [
            'label_col'     => $options['label_col'],
            'widget_col'    => $options['widget_col'],
            'label'         => $options['label'],
            'trans_domain'  => $options['trans_domain'],
            'attr'          => $attr,
            'value'         => $value,
            'row_prefix'    => $options['row_prefix'] ?: $this->getRowPrefix(),
            'widget_prefix' => $options['widget_prefix'] ?: $this->getWidgetPrefix(),
        ]);
    }

    /**
     * @inheritDoc
     */
    public function getRowPrefix()
    {
        return 'default';
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'default';
    }

    /**
     * Configures the type's options.
     *
     * @param OptionsResolver $resolver
     */
    protected function configureOptions(OptionsResolver $resolver)
    {

    }

    /**
     * Returns the option resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionResolver()
    {
        if (null !== $this->optionResolver) {
            return $this->optionResolver;
        }

        $this->optionResolver = new OptionsResolver();
        $this->optionResolver
            ->setDefaults([
                'id'            => null,
                'class'         => null,
                'label'         => null,
                'trans_domain'  => null,
                'label_col'     => 2,
                'widget_col'    => 10,
                'row_prefix'    => null,
                'widget_prefix' => null,
                'attr'          => [],
            ])
            ->setAllowedTypes('id', ['null', 'string'])
            ->setAllowedTypes('label', ['null', 'string'])
            ->setAllowedTypes('trans_domain', ['null', 'bool', 'string'])
            ->setAllowedTypes('class', ['null', 'string'])
            ->setAllowedTypes('label_col', 'int')
            ->setAllowedTypes('widget_col', 'int')
            ->setAllowedTypes('row_prefix', ['null', 'string'])
            ->setAllowedTypes('widget_prefix', ['null', 'string'])
            ->setAllowedTypes('attr', 'array')
            ->setNormalizer('trans_domain', function (Options $options, $value) {
                if (true === $value) {
                    return null;
                }

                return $value;
            })
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