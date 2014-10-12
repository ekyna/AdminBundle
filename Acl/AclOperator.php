<?php

namespace Ekyna\Bundle\AdminBundle\Acl;

use Ekyna\Bundle\AdminBundle\Form\Type\PermissionType;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\UserBundle\Model\GroupInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;
use Symfony\Component\Security\Core\SecurityContextInterface;

/**
 * Class AclOperator
 * @package Ekyna\Bundle\AdminBundle\Acl
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AclOperator implements AclOperatorInterface
{
    /**
     * @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var \Symfony\Component\Security\Acl\Permission\PermissionMapInterface
     */
    protected $permissionMap;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var \Symfony\Component\Security\Core\SecurityContextInterface
     */
    protected $security;

    /**
     * Constructor.
     * 
     * @param MutableAclProviderInterface $aclProvider
     * @param PermissionMapInterface $permissionMap
     * @param ConfigurationRegistry $registry
     * @param SecurityContextInterface $securityContext
     */
    public function __construct(
        MutableAclProviderInterface $aclProvider, 
        PermissionMapInterface $permissionMap, 
        ConfigurationRegistry $registry,
        SecurityContextInterface $securityContext
    ) {
        $this->aclProvider   = $aclProvider;
        $this->permissionMap = $permissionMap;
        $this->registry      = $registry;
        $this->security      = $securityContext;
    }

    /**
     * {@inheritDoc}
     */
    public function loadAcls()
    {
        $oids = [];
        foreach ($this->registry->getConfigurations() as $config) {
            $oids[] = $config->getObjectIdentity();
        }
        $this->aclProvider->findAcls($oids);
    }

    /**
     * {@inheritDoc}
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = array())
    {
        return $this->aclProvider->findAcl($oid, $sids);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMask(ObjectIdentity $oid, RoleSecurityIdentity $rid)
    {
        try {
            $acl = $this->aclProvider->findAcl($oid);
            foreach($acl->getClassAces() as $index => $entry) {
                if($entry->getSecurityIdentity()->equals($rid)) {
                    return $entry->getMask();
                }
            }
        }catch(\Exception $e) {
        }
        return 0;
    }

    /**
     * {@inheritDoc}
     */
    public function setClassMask(ObjectIdentity $oid, RoleSecurityIdentity $rid, $mask = 0)
    {
        try {
            // Try updating existing Ace 
            $acl = $this->aclProvider->findAcl($oid);
            foreach($acl->getClassAces() as $index => $entry) {
                if($entry->getSecurityIdentity()->equals($rid)) {
                    if($entry->getMask() != $mask) {
                        $acl->updateClassAce($index, $mask);
                        $this->aclProvider->updateAcl($acl);
                    }
                    return;
                }
            }

            // Create Ace
            $acl->insertClassAce($rid, $mask);
            $this->aclProvider->updateAcl($acl);
            return;

        }catch(AclNotFoundException $e) { // TODO: Catch only acl/ace exception ?
        }

        // Create Acl and Ace
        $acl = $this->aclProvider->createAcl($oid);
        $acl->insertClassAce($rid, $mask);
        $this->aclProvider->updateAcl($acl);
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionMap()
    {
        return $this->permissionMap;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissions()
    {
        if(null === $this->permissions) {
            $this->permissions = array();
            $reflexion = new \ReflectionClass($this->permissionMap);
            foreach($reflexion->getConstants() as $name => $value) {
                if(substr($name, 0, 10) == 'PERMISSION') {
                    $this->permissions[] = strtolower($value);
                }
            }
        }
        return $this->permissions;
    }

    /**
     * {@inheritDoc}
     */
    public function getPermissionMasks($permission)
    {
        return $this->permissionMap->getMasks(strtoupper($permission), null);
    }

    /**
     * {@inheritDoc}
     */
    public function buildGroupForm(FormBuilderInterface $builder)
    {
        foreach ($this->registry->getConfigurations() as $config) {
            $builder->add($config->getAlias(), new PermissionType($this->getPermissions()), array(
                'label' => $config->getResourceName()
            ));
        }
    }

    /**
     * {@inheritDoc}
     */
    public function generateGroupFormDatas(GroupInterface $group)
    {
        $datas = array();
        $rid = $group->getSecurityIdentity();
        $permissions = $this->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $mask = $this->getClassMask($config->getObjectIdentity(), $rid);

            $oidDatas = array();
            foreach ($permissions as $permission) {
                $permissionMask = $this->getPermissionMasks(strtoupper($permission))[0];
                $oidDatas[$permission] = $permissionMask === ($mask & $permissionMask);
            }
            $datas[$config->getAlias()] = $oidDatas;
        }

        return $datas;
    }
    
    /**
     * {@inheritDoc}
     */
    public function generateGroupViewDatas(GroupInterface $group)
    {
        $datas = array();
        $rid = $group->getSecurityIdentity();
        $permissions = $this->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $oidDatas = array();
            $acl = false;
            try {
                $acl = $this->findAcl($config->getObjectIdentity());
            }catch(\Exception $e) {
                $acl = false;
            }

            if (false !== $acl) {
                foreach ($permissions as $permission) {
                    try {
                        $granted = $acl->isGranted($this->getPermissionMasks(strtoupper($permission)), array($rid));
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
     * {@inheritDoc}
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
                        $masks = $this->getPermissionMasks($permission);
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

            $this->setClassMask($config->getObjectIdentity(), $rid, $maskBuilder->get());
        }
    }

    /**
     * {@inheritDoc}
     */
    public function isAccessGranted($resource, $permission)
    {
        $permission = strtoupper($permission);
        if (! $this->getPermissionMap()->contains($permission)) {
            throw new \InvalidArgumentException(sprintf('Unknown permission "%s".', $permission));
        }

        $config = $this->registry->findConfiguration($resource);

        return $this->security->isGranted($permission, $config->getObjectIdentity());
    }
}
