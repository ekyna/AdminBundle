<?php

namespace Ekyna\Bundle\AdminBundle\Settings;

use Ekyna\Bundle\SettingBundle\Schema\SchemaInterface;
use Ekyna\Bundle\SettingBundle\Schema\SettingsBuilderInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\Email;

/**
 * GeneralSettingsSchema.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class GeneralSettingsSchema implements SchemaInterface
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
                'sitename'   => 'Default website name',
                'adminname'  => 'Default admin name',
                'adminemail' => 'contact@example.org',
            ), $this->defaults))
            ->setAllowedTypes(array(
                'sitename'   => array('string'),
                'adminname'  => array('string'),
                'adminemail' => array('string'),
            ))
        ;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder)
    {
        $builder
            ->add('sitename', 'text', array(
                'label'       => 'Site name',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('adminname', 'textarea', array(
                'label'       => 'Admin name',
                'constraints' => array(
                    new NotBlank()
                )
            ))
            ->add('adminemail', 'locale', array(
                'label'       => 'Admin email',
                'constraints' => array(
                    new NotBlank(),
                    new Email(),
                )
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
}
