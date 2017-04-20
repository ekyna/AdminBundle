<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Model\SiteAddress;
use Ekyna\Bundle\GoogleBundle\Form\Type\CoordinateType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class SiteAddressType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SiteAddressType extends AbstractType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('street', Type\TextType::class, [
                'label' => t('field.street', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'address-street',
                ],
            ])
            ->add('supplement', Type\TextType::class, [
                'label'    => t('field.supplement', [], 'EkynaUi'),
                'attr'     => [
                    'class' => 'address-supplement',
                ],
                'required' => false,
            ])
            ->add('postalCode', Type\TextType::class, [
                'label' => t('field.postal_code', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'address-postal-code',
                ],
            ])
            ->add('city', Type\TextType::class, [
                'label' => t('field.city', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'address-city',
                ],
            ])
            ->add('country', Type\CountryType::class, [
                'label' => t('field.country', [], 'EkynaUi'),
                'attr'  => [
                    'class' => 'address-country',
                ],
            ])
            ->add('state', Type\TextType::class, [
                'label'    => t('field.state', [], 'EkynaUi'),
                'attr'     => [
                    'class' => 'address-state',
                ],
                'required' => false,
            ])
            ->add('phone', Type\TextType::class, [
                'label'    => t('field.phone', [], 'EkynaUi'),
                'attr'     => [
                    'class' => 'address-phone',
                ],
                'required' => false,
            ])
            ->add('mobile', Type\TextType::class, [
                'label'    => t('field.mobile', [], 'EkynaUi'),
                'attr'     => [
                    'class' => 'address-mobile',
                ],
                'required' => false,
            ])
            ->add('coordinate', CoordinateType::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'data_class' => SiteAddress::class,
            ]);
    }
}
