<?php

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\Extension\Core\Type\FormType;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class AdminTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Form\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'admin_mode'   => false,
                'admin_helper' => null,
            ])
            ->setAllowedTypes('admin_mode', 'bool')
            ->setAllowedTypes('admin_helper', ['null', 'string']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (0 < strlen($options['admin_helper'])) {
            $view->vars['attr']['data-helper'] = $options['admin_helper'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
        return FormType::class;
    }
}
