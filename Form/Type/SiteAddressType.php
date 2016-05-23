<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\AddressType;
use Ekyna\Bundle\GoogleBundle\Form\Type\CoordinateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SiteAddressType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SiteAddressType extends AbstractType
{
    /**
     * {@inheritDoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('phone', TextType::class, [
                'label'    => 'ekyna_core.field.phone',
                'required' => false,
            ])
            ->add('mobile', TextType::class, [
                'label'    => 'ekyna_core.field.mobile',
                'required' => false,
            ])
            ->add('coordinate', CoordinateType::class)
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'data_class' => 'Ekyna\Bundle\AdminBundle\Model\SiteAddress',
            ]);
    }

    /**
     * {@inheritDoc}
     */
    public function getParent()
    {
        return AddressType::class;
    }
}
