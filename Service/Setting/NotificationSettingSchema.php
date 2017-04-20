<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Setting;

use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Ekyna\Bundle\UiBundle\Form\Type\CollectionType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class NotificationSettingSchema
 * @package Ekyna\Bundle\AdminBundle\Settings
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NotificationSettingSchema extends AbstractSchema
{
    public function buildSettings(SettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(array_merge([
                'from_name'  => 'Default admin name',
                'from_email' => 'contact@example.org',
                'no_reply'   => 'contact@example.org',
                'to_emails'  => ['contact@example.org'],
            ], $this->defaults))
            ->setAllowedTypes('from_name', 'string')
            ->setAllowedTypes('from_email', 'string')
            ->setAllowedTypes('no_reply', 'string')
            ->setAllowedTypes('to_emails', 'array');
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('from_name', TextType::class, [
                'label'       => t('settings.notification.from_name', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('from_email', TextType::class, [
                'label'       => t('settings.notification.from_email', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ])
            ->add('no_reply', TextType::class, [
                'label'       => t('settings.notification.no_reply', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ])
            ->add('to_emails', CollectionType::class, [
                'label'          => t('settings.notification.to_emails', [], 'EkynaAdmin'),
                'sub_widget_col' => 10,
                'button_col'     => 2,
                'constraints'    => [
                    new Constraints\All([
                        'constraints' => [
                            new Constraints\NotBlank(),
                            new Constraints\Email(),
                        ],
                    ]),
                    new Constraints\Count([
                        'min'        => 1,
                        'minMessage' => 'settings.notification.at_least_one_email',
                    ]),
                ],
            ]);
    }

    public function getLabel(): TranslatableInterface
    {
        return t('settings.notification.label', [], 'EkynaAdmin');
    }

    public function getShowTemplate(): string
    {
        return '@EkynaAdmin/Settings/Notification/show.html.twig';
    }

    public function getFormTemplate(): string
    {
        return '@EkynaAdmin/Settings/Notification/form.html.twig';
    }
}
