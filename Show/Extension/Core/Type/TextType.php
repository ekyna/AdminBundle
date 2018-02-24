<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TextType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TextType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        parent::build($view, $value, $options);

        $view->vars['trans_domain'] = $options['trans_domain'];
        $view->vars['trans_params'] = $options['trans_params'];
    }

    /**
     * @inheritDoc
     */
    protected function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver
            ->setDefaults([
                'trans_domain' => false,
                'trans_params' => [],
            ])
            ->setAllowedTypes('trans_domain', ['null', 'bool', 'string'])
            ->setAllowedTypes('trans_params', 'array')
            ->setNormalizer('trans_domain', function (Options $options, $value) {
                if (true === $value) {
                    return null;
                }

                return $value;
            });
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'text';
    }
}