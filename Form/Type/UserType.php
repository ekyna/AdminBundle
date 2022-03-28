<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function Symfony\Component\Translation\t;

/**
 * Class UserType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserType extends AbstractResourceType
{
    private AuthorizationCheckerInterface $authorizationChecker;

    public function __construct(AuthorizationCheckerInterface $authorizationChecker)
    {
        $this->authorizationChecker = $authorizationChecker;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $builder
            ->add('email', Type\EmailType::class, [
                'label' => t('field.email', [], 'EkynaUi'),
            ])
            ->add('group', ResourceChoiceType::class, [
                'label'    => t('field.group', [], 'EkynaUi'),
                'resource' => 'ekyna_admin.group',
                'disabled' => !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
            ])
            ->add('enabled', Type\CheckboxType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('firstName', Type\TextType::class, [
                'label'    => t('field.first_name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('lastName', Type\TextType::class, [
                'label'    => t('field.last_name', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('position', Type\TextType::class, [
                'label'    => t('user.field.position', [], 'EkynaAdmin'),
                'required' => false,
            ])
            ->add('phone', Type\TextType::class, [
                'label'    => t('field.phone', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('mobile', Type\TextType::class, [
                'label'    => t('field.mobile', [], 'EkynaUi'),
                'required' => false,
            ])
            ->add('emailConfig', EmailConfigType::class, [
                'required' => false,
            ])
        ;
    }
}
