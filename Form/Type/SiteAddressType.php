<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Util\FormUtil;
use Ekyna\Bundle\GoogleBundle\Form\Type\CoordinateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
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
            ->add('street', Type\TextType::class, [
                'label' => 'ekyna_core.field.street',
                'attr'  => [
                    'class' => 'address-street',
                ],
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => 'ekyna_core.field.supplement',
                'attr'     => [
                    'class' => 'address-supplement',
                ],
                'required' => false,
            ])
            ->add('postalCode', Type\TextType::class, [
                'label' => 'ekyna_core.field.postal_code',
                'attr'  => [
                    'class' => 'address-postal-code',
                ],
            ])
            ->add('city', Type\TextType::class, [
                'label' => 'ekyna_core.field.city',
                'attr'  => [
                    'class' => 'address-city',
                ],
            ])
            ->add('country', Type\CountryType::class, [
                'label' => 'ekyna_core.field.country',
                'attr'  => [
                    'class' => 'address-country',
                ],
            ])
            ->add('state', Type\TextType::class, [
                'label'    => 'ekyna_core.field.state',
                'attr'     => [
                    'class' => 'address-state',
                ],
                'required' => false,
            ])
            ->add('phone', Type\TextType::class, [
                'label'    => 'ekyna_core.field.phone',
                'attr'     => [
                    'class' => 'address-phone',
                ],
                'required' => false,
            ])
            ->add('mobile', Type\TextType::class, [
                'label'    => 'ekyna_core.field.mobile',
                'attr'     => [
                    'class' => 'address-mobile',
                ],
                'required' => false,
            ])
            ->add('coordinate', CoordinateType::class);
    }

    /**
     * @inheritDoc
     */
    public function finishView(FormView $view, FormInterface $form, array $options)
    {
        FormUtil::addClass($view, '.address');
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
}
