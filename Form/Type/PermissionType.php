<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PermissionType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionType extends AbstractType
{
    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($options['permissions'] as $permission) {
            $builder
                ->add($permission, CheckboxType::class, [
                    'label'    => ucfirst($permission),
                    'required' => false,
                ]);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefined('permissions')
            ->setRequired('permissions')
            ->setAllowedTypes('permissions', 'array');
    }

    /**
     * {@inheritdoc}
     */
    public function getBlockPrefix()
    {
        return 'ekyna_admin_permission';
    }
}
