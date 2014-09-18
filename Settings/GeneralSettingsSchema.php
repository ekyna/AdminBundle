<?php

namespace Ekyna\Bundle\AdminBundle\Settings;

use Ekyna\Bundle\AdminBundle\Form\Type\SiteAddressType;
use Ekyna\Bundle\AdminBundle\Model\SiteAddress;
use Ekyna\Bundle\SettingBundle\Schema\AbstractSchema;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\Email;
use Symfony\Component\Validator\Constraints\NotBlank;

/**
 * Class GeneralSettingsSchema
 * @package Ekyna\Bundle\AdminBundle\Settings
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GeneralSettingsSchema extends AbstractSchema
{
    /**
     * @var array
     */
    protected $defaults;

    /**
     * @param array $defaults
     */
    public function __construct(array $defaults = array())
    {
        $this->defaults = $defaults;
    }

    /**
     * {@inheritdoc}
     */
    public function buildSettings(SettingsBuilderInterface $builder)
    {
        $builder
            ->setDefaults(array_merge(array(
                'site_name'    => 'Default website name',
                'admin_name'   => 'Default admin name',
                'admin_email'  => 'contact@example.org',
                'site_address' => new SiteAddress(),
            ), $this->defaults))
            ->setAllowedTypes(array(
                'site_name'    => 'string',
                'admin_name'   => 'string',
                'admin_email'  => 'string',
                'site_address' => 'Ekyna\Bundle\AdminBundle\Model\SiteAddress'
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options = array())
    {
        $builder
            ->add('site_name', 'text', array(
                'label'       => 'ekyna_admin.setting.sitename',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('admin_name', 'text', array(
                'label'       => 'ekyna_admin.setting.adminname',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('admin_email', 'text', array(
                'label'       => 'ekyna_admin.setting.adminemail',
                'constraints' => array(
                    new NotBlank(),
                    new Email(),
                )
            ))
            ->add('site_address', new SiteAddressType(), array(
                'label' => 'ekyna_admin.setting.siteaddress',
                'cascade_validation' => true
            ))
        ;
    }

    /**
     * {@inheritDoc}
     */
    public function getLabel()
    {
        return 'ekyna_core.field.general';
    }

    /**
     * {@inheritDoc}
     */
    public function getShowTemplate()
    {
        return 'EkynaAdminBundle:Settings:show.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getFormTemplate()
    {
        return 'EkynaAdminBundle:Settings:form.html.twig';
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ekyna_general_settings';
    }
}
