<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\TinymceType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class UserType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserType extends ResourceFormType
{
    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var string
     */
    protected $groupClass;


    /**
     * Constructor.
     *
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param string                        $userClass
     * @param string                        $groupClass
     */
    public function __construct(AuthorizationCheckerInterface $authorizationChecker, $userClass, $groupClass)
    {
        parent::__construct($userClass);

        $this->authorizationChecker = $authorizationChecker;
        $this->groupClass = $groupClass;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('group', EntityType::class, [
                'label'        => 'ekyna_core.field.group',
                'class'        => $this->groupClass,
                'choice_label' => 'name',
                'disabled'     => !$this->authorizationChecker->isGranted('ROLE_SUPER_ADMIN'),
            ])
            ->add('email', Type\EmailType::class, [
                'label' => 'ekyna_core.field.email',
            ])
            ->add('firstName', Type\TextType::class, [
                'label'    => 'ekyna_core.field.first_name',
                'required' => false,
            ])
            ->add('lastName', Type\TextType::class, [
                'label'    => 'ekyna_core.field.last_name',
                'required' => false,
            ])
            ->add('active', Type\CheckboxType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'required' => false,
                'attr'     => [
                    'align_with_widget' => true,
                ],
            ])
            ->add('emailConfig', EmailConfigType::class, [
                'required' => false,
            ])
            ->add('emailSignature', TinymceType::class, [
                'theme'    => 'advanced',
                'required' => false,
            ]);
    }
}
