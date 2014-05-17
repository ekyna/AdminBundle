<?php

namespace Ekyna\Bundle\AdminBundle\Acl;

use Ekyna\Bundle\AdminBundle\Form\Type\PermissionType;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\UserBundle\Model\GroupInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;

/**
 * AclManipulator.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AclManipulator
{
    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Acl\AclEditor
     */
    protected $aclEditor;

    /**
     * Constructor.
     * 
     * @param ConfigurationRegistry $registry
     * @param AclEditor             $aclEditor
     */
    public function __construct(ConfigurationRegistry $registry, AclEditor $aclEditor)
    {
        $this->registry = $registry;
        $this->aclEditor = $aclEditor;
    }

    /**
     * {@inheritdoc}
     */
    public function buildGroupForm(FormBuilderInterface $builder)
    {
        foreach ($this->registry->getConfigurations() as $config) {
            $builder->add($config->getAlias(), new PermissionType($this->aclEditor->getPermissions()), array(
            	'label' => $config->getResourceName()
            ));
        }
    }

    /**
     * {@inheritdoc}
     */
    public function generateGroupFormDatas(GroupInterface $group)
    {
        $datas = array();
        $rid = $group->getSecurityIdentity();
        $permissions = $this->aclEditor->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $mask = $this->aclEditor->getClassMask($config->getObjectIdentity(), $rid);

            $oidDatas = array();
            foreach ($permissions as $permission) {
                $permissionMask = $this->aclEditor->getPermissionMasks(strtoupper($permission))[0];
                $oidDatas[$permission] = $permissionMask === ($mask & $permissionMask);
            }
            $datas[$config->getAlias()] = $oidDatas;
        }

        return $datas;
    }

    /**
     * {@inheritdoc}
     */
    public function generateGroupViewDatas(GroupInterface $group)
    {
        $datas = array();
        $rid = $group->getSecurityIdentity();
        $permissions = $this->aclEditor->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $oidDatas = array();
            $acl = false;
            try {
                $acl = $this->aclEditor->findAcl($config->getObjectIdentity());
            }catch(\Exception $e) {
                $acl = false;
            }

            if (false !== $acl) {
                foreach ($permissions as $permission) {
                    try {
                        $granted = $acl->isGranted($this->aclEditor->getPermissionMasks(strtoupper($permission)), array($rid));
                        $oidDatas[$permission] = $granted;
                    } catch(\Exception $e) {
                        $oidDatas[$permission] = false;
                    }
                }
            } else {
                foreach ($permissions as $permission) {
                    $oidDatas[$permission] = false;
                }
            }
            $datas[$config->getResourceName()] = $oidDatas;
        }

        return $datas;
    }

    /**
     * {@inheritdoc}
     */
    public function updateGroup(GroupInterface $group, array $datas)
    {
        $rid = $group->getSecurityIdentity();
        $maskBuilder = new MaskBuilder();

        foreach ($datas as $configName => $oidDatas) {
            $config = $this->registry->get($configName);

            $retainedPermissions = array();
            $oidDatas = array_reverse($oidDatas);
            foreach ($oidDatas as $permission => $enabled) {
                if ($enabled) {
                    $permission = strtoupper($permission);
                    if (empty($retainedPermissions)) {
                        $retainedPermissions[] = $permission;
                    } else {
                        $masks = $this->aclEditor->getPermissionMasks($permission);
                        $add = true;
                        foreach ($retainedPermissions as $p) {
                            $maskBuilder->reset();
                            $maskBuilder->add($p);
                            $mask = $maskBuilder->get();
                            if(in_array($mask, $masks)) {
                                $add = false;
                                break;
                            }
                        }
                        if ($add) {
                            $retainedPermissions[] = $permission;
                        }
                    }
                }
            }

            $maskBuilder->reset();
            foreach ($retainedPermissions as $p) {
                $maskBuilder->add($p);
            }

            $this->aclEditor->setClassMask($config->getObjectIdentity(), $rid, $maskBuilder->get());
        }
    }
}
