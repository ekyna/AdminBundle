<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * Class PermissionType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionType extends AbstractType
{
    /**
     * @var array
     */
    protected $permissions;

    /**
     * Constructor.
     *
     * @param array $permissions
     */
    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->permissions as $permission) {
            $builder
                ->add($permission, 'checkbox', [
                    'label' => ucfirst($permission),
                    'required' => false
                ])
            ;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
    	return 'ekyna_admin_permission';
    }
}