<?php

namespace Ekyna\Bundle\AdminBundle\Settings;

use Ekyna\Bundle\CoreBundle\Form\Type\CollectionType;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class NotificationSettingsSchema
 * @package Ekyna\Bundle\AdminBundle\Settings
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class NotificationSettingsSchema extends AbstractSchema
{
    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilder $builder)
    {
        $builder
            ->setDefaults(array_merge([
                'from_name'  => 'Default admin name',
                'from_email' => 'contact@example.org',
                'to_emails'  => ['contact@example.org'],
            ], $this->defaults))
            ->setAllowedTypes('from_name',  'string')
            ->setAllowedTypes('from_email', 'string')
            ->setAllowedTypes('to_emails',  'array')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from_name', TextType::class, [
                'label' => 'ekyna_admin.settings.notification.from_name',
                'constraints' => [
                    new Constraints\NotBlank()
                ]
            ])
            ->add('from_email', TextType::class, [
                'label' => 'ekyna_admin.settings.notification.from_email',
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ]
            ])
            ->add('to_emails', CollectionType::class, [
                'label'           => 'ekyna_admin.settings.notification.to_emails',
                'entry_type'      => TextType::class,
                'allow_add'       => true,
                'allow_delete'    => true,
                'add_button_text' => 'ekyna_core.button.add',
                'sub_widget_col'  => 10,
                'button_col'      => 2,
                'constraints'     => [
                    new Constraints\All([
                        'constraints' => [
                            new Constraints\NotBlank(),
                            new Constraints\Email(),
                        ],
                    ]),
                    new Constraints\Count([
                        'min'        => 1,
                        'minMessage' => 'ekyna_admin.settings.notification.at_least_one_email',
                    ]),
                ],
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'ekyna_admin.settings.notification.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getShowTemplate()
    {
        return 'EkynaAdminBundle:Settings/Notification:show.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormTemplate()
    {
        return 'EkynaAdminBundle:Settings/Notification:form.html.twig';
    }
}
