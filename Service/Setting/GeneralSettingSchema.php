<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Setting;

use Ekyna\Bundle\AdminBundle\Form\Type\SiteAddressType;
use Ekyna\Bundle\AdminBundle\Model\SiteAddress;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

use Symfony\Contracts\Translation\TranslatableInterface;

use function Symfony\Component\Translation\t;

/**
 * Class GeneralSettingSchema
 * @package Ekyna\Bundle\AdminBundle\Service\Setting
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class GeneralSettingSchema extends AbstractSchema
{
    public function buildSettings(SettingsBuilder $builder): void
    {
        $builder
            ->setDefaults(array_merge([
                'site_name'    => 'Default website name',
                'admin_name'   => 'Default admin name',
                'admin_email'  => 'contact@example.org',
                'site_address' => new SiteAddress(),
            ], $this->defaults))
            ->setAllowedTypes('site_name', 'string')
            ->setAllowedTypes('admin_name', 'string')
            ->setAllowedTypes('admin_email', 'string')
            ->setAllowedTypes('site_address', SiteAddress::class);
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site_name', TextType::class, [
                'label'       => t('settings.general.site_name', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('admin_name', TextType::class, [
                'label'       => t('settings.general.admin_name', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                ],
            ])
            ->add('admin_email', TextType::class, [
                'label'       => t('settings.general.admin_email', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ],
            ])
            ->add('site_address', SiteAddressType::class, [
                'label'       => t('settings.general.site_address', [], 'EkynaAdmin'),
                'constraints' => [
                    new Constraints\Valid(),
                ],
            ]);
    }

    public function getLabel(): TranslatableInterface
    {
        return t('settings.general.label', [], 'EkynaAdmin');
    }

    public function getShowTemplate(): string
    {
        return '@EkynaAdmin/Settings/General/show.html.twig';
    }

    public function getFormTemplate(): string
    {
        return '@EkynaAdmin/Settings/General/form.html.twig';
    }
}
