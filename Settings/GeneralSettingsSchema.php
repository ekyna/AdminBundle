<?php

namespace Ekyna\Bundle\AdminBundle\Settings;

use Ekyna\Bundle\AdminBundle\Form\Type\SiteAddressType;
use Ekyna\Bundle\AdminBundle\Model\SiteAddress;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilder;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class GeneralSettingsSchema
 * @package Ekyna\Bundle\AdminBundle\Settings
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GeneralSettingsSchema extends AbstractSchema
{
    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilder $builder)
    {
        $builder
            ->setDefaults(array_merge([
                'site_name'         => 'Default website name',
                'admin_name'        => 'Default admin name',
                'admin_email'       => 'contact@example.org',
                'site_address'      => new SiteAddress(),
            ], $this->defaults))
            ->setAllowedTypes('site_name',    'string')
            ->setAllowedTypes('admin_name',   'string')
            ->setAllowedTypes('admin_email',  'string')
            ->setAllowedTypes('site_address', 'Ekyna\Bundle\AdminBundle\Model\SiteAddress')
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder
            ->add('site_name', 'text', [
                'label' => 'ekyna_admin.settings.general.site_name',
                'constraints' => [
                    new Constraints\NotBlank()
                ]
            ])
            ->add('admin_name', 'text', [
                'label' => 'ekyna_admin.settings.general.admin_name',
                'constraints' => [
                    new Constraints\NotBlank()
                ]
            ])
            ->add('admin_email', 'text', [
                'label' => 'ekyna_admin.settings.general.admin_email',
                'constraints' => [
                    new Constraints\NotBlank(),
                    new Constraints\Email(),
                ]
            ])
            ->add('site_address', new SiteAddressType(), [
                'label' => 'ekyna_admin.settings.general.siteaddress',
                'cascade_validation' => true,
            ])
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'ekyna_admin.settings.general.label';
    }

    /**
     * {@inheritDoc}
     */
    public function getShowTemplate()
    {
        return 'EkynaAdminBundle:Settings/General:show.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormTemplate()
    {
        return 'EkynaAdminBundle:Settings/General:form.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ekyna_admin_settings_general';
    }
}
