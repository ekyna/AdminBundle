<?php

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
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
        $resolver->setDefaults(array(
        	'admin_mode' => false,
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function getExtendedType()
    {
    	return 'form';
    }
}
