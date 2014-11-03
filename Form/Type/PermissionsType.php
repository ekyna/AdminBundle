<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;

/**
 * Class PermissionsType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionsType extends AbstractType
{
    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $registry
     * @param array                 $permissions
     */
    public function __construct(ConfigurationRegistry $registry, array $permissions)
    {
        $this->registry    = $registry;
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach ($this->registry->getConfigurations() as $config) {
            $builder->add($config->getAlias(), new PermissionType($this->permissions), array(
                'label' => $config->getResourceLabel(true)
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['permissions'] = $this->permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'ekyna_admin_permissions';
    }
}
