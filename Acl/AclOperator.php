<?php

namespace Ekyna\Bundle\AdminBundle\Acl;

use Ekyna\Bundle\AdminBundle\Form\Type\PermissionsType;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\UserBundle\Model\GroupInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;
use Symfony\Component\Security\Acl\Exception\NotAllAclsFoundException;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Permission\MaskBuilder;
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AclOperator
 * @package Ekyna\Bundle\AdminBundle\Acl
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AclOperator implements AclOperatorInterface
{
    /**
     * @var MutableAclProviderInterface
     */
    protected $aclProvider;

    /**
     * @var PermissionMapInterface
     */
    protected $permissionMap;

    /**
     * @var array
     */
    protected $permissions;

    /**
     * @var ConfigurationRegistry
     */
    protected $registry;

    /**
     * @var AuthorizationCheckerInterface
     */
    protected $security;

    /**
     * Constructor.
     *
     * @param MutableAclProviderInterface $aclProvider
     * @param PermissionMapInterface $permissionMap
     * @param ConfigurationRegistry $registry
     * @param AuthorizationCheckerInterface $security
     */
    public function __construct(
        MutableAclProviderInterface $aclProvider,
        PermissionMapInterface $permissionMap,
        ConfigurationRegistry $registry,
        AuthorizationCheckerInterface $security
    ) {
        $this->aclProvider   = $aclProvider;
        $this->permissionMap = $permissionMap;
        $this->registry      = $registry;
        $this->security      = $security;
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

        try {
            $this->aclProvider->findAcls($oids);
        } catch(NotAllAclsFoundException $e) {
            // If acls has not been updated yet.
        }
    }

    /**
     * {@inheritDoc}
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = [])
    {
        return $this->aclProvider->findAcl($oid, $sids);
    }

    /**
     * {@inheritDoc}
     */
    public function getClassMask(ObjectIdentity $oid, RoleSecurityIdentity $rid)
    {
        try {
            /** @var \Symfony\Component\Security\Acl\Domain\Acl $acl */
            $acl = $this->aclProvider->findAcl($oid);
            /** @var \Symfony\Component\Security\Acl\Model\EntryInterface $entry */
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
            /** @var \Symfony\Component\Security\Acl\Domain\Acl $acl */
            $acl = $this->aclProvider->findAcl($oid);
            /** @var \Symfony\Component\Security\Acl\Model\EntryInterface $entry */
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
            $this->permissions = [];
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
        $builder->add('acls', new PermissionsType($this->registry, $this->permissions), [
            'label' => false,
        ]);
    }

    /**
     * {@inheritDoc}
     */
    public function generateGroupFormDatas(GroupInterface $group)
    {
        $datas = [];
        $rid = $group->getSecurityIdentity();
        $permissions = $this->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $mask = $this->getClassMask($config->getObjectIdentity(), $rid);

            $oidDatas = [];
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
        $datas = [];
        $rid = $group->getSecurityIdentity();
        $permissions = $this->getPermissions();

        foreach ($this->registry->getConfigurations() as $config) {
            $oidDatas = [];
            try {
                $acl = $this->findAcl($config->getObjectIdentity());
            }catch(\Exception $e) {
                $acl = false;
            }

            if (false !== $acl) {
                foreach ($permissions as $permission) {
                    try {
                        $granted = $acl->isGranted($this->getPermissionMasks(strtoupper($permission)), [$rid]);
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
            $datas[$config->getResourceLabel(true)] = $oidDatas;
        }

        return $datas;
    }

    /**
     * {@inheritDoc}
     */
    public function updateGroup(GroupInterface $group, array $data)
    {
        $rid = $group->getSecurityIdentity();
        $maskBuilder = new MaskBuilder();

        foreach ($data as $configName => $oidData) {
            $config = $this->registry->get($configName);

            $retainedPermissions = [];
            $oidData = array_reverse($oidData);
            foreach ($oidData as $permission => $enabled) {
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
