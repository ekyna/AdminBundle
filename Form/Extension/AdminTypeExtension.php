<?php

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class AdminTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Form\Extension
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminTypeExtension extends AbstractTypeExtension
{
    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver
            ->setDefaults(array(
                'admin_mode' => false,
                'admin_helper' => null,
            ))
            ->setAllowedTypes(array(
                'admin_mode' => 'bool',
                'admin_helper' => array('null', 'string'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        if (0 < strlen($options['admin_helper'])) {
            $view->vars['admin_helper'] = $options['admin_helper'];
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
    	return 'form';
    }
}
