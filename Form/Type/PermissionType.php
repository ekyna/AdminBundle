<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

/**
 * PermissionType
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PermissionType extends AbstractType
{
    /**
     * @var array
     */
    protected $permissions;

    public function __construct(array $permissions)
    {
        $this->permissions = $permissions;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        foreach($this->permissions as $permission) {
            $builder
                ->add($permission, 'checkbox', array(
                    'label' => ucfirst($permission),
                    'required' => false
                ))
            ;
        }
    }

    public function getName()
    {
    	return 'ekyna_admin_permission';
    }
}