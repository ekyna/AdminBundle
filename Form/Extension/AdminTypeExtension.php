<?php

namespace Ekyna\Bundle\AdminBundle\Form\Extension;

use Symfony\Component\Form\AbstractTypeExtension;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * AdminTypeExtension
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminTypeExtension extends AbstractTypeExtension
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
        	'admin_mode' => false,
        ));
    }

    public function getExtendedType()
    {
    	return 'form';
    }
}
