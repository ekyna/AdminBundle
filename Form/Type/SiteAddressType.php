<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\AbstractAddressType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class SiteAddressType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class SiteAddressType extends AbstractAddressType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        parent::buildForm($builder, $options);
    
        $builder
            ->add('phone', 'text', array(
                'label' => 'ekyna_core.field.phone',
                'required' => false
            ))
            ->add('mobile', 'text', array(
                'label' => 'ekyna_core.field.mobile',
                'required' => false
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
	public function setDefaultOptions(OptionsResolverInterface $resolver)
	{
	    $resolver
	       ->setDefaults(array(
	       	   'data_class' => 'Ekyna\Bundle\AdminBundle\Model\SiteAddress',
	       ))
	    ;
	}

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
    	return 'ekyna_admin_site_address';
    }
}
