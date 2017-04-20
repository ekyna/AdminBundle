<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use function Symfony\Component\Translation\t;

/**
 * Class GroupType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupType extends AbstractResourceType
{
    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder->addEventListener(FormEvents::PRE_SET_DATA, function (FormEvent $event) {
            $group = $event->getData();
            $form = $event->getForm();

            $form
                ->add('name', Type\TextType::class, [
                    'label'    => t('field.name', [], 'EkynaUi'),
                    'disabled' => $group && $group->getId(),
                ]);
        });

        $builder
            ->add('roles', Type\ChoiceType::class, [
                'label'                     => t('field.roles', [], 'EkynaUi'),
                'expanded'                  => true,
                'multiple'                  => true,
                'choice_translation_domain' => 'EkynaUi',
                'choices'                   => [
                    'auth.allowed_to_switch' => 'ROLE_ALLOWED_TO_SWITCH',
                    'auth.super_admin'       => 'ROLE_SUPER_ADMIN',
                    'auth.admin'             => 'ROLE_ADMIN',
                ],
            ]);
    }
}
