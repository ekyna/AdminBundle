<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class GroupType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupType extends ResourceFormType
{
    /**
     * @inheritDoc
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('name', Type\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'disabled' => true,
            ])
            ->add('roles', Type\ChoiceType::class, [
                'label'    => 'ekyna_core.field.roles',
                'expanded' => true,
                'multiple' => true,
                'choices'  => [
                    'ekyna_core.auth.allowed_to_switch' => 'ROLE_ALLOWED_TO_SWITCH',
                    'ekyna_core.auth.super_admin'       => 'ROLE_SUPER_ADMIN',
                    'ekyna_core.auth.admin'             => 'ROLE_ADMIN',
                ],
            ]);
    }
}
