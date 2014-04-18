<?php

namespace Ekyna\Bundle\AdminBundle\Acl;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\MutableAclProviderInterface;
use Symfony\Component\Security\Acl\Permission\PermissionMapInterface;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;
use Symfony\Component\Security\Acl\Exception\AclNotFoundException;

/**
 * AclEditor
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AclEditor
{
    /**
     * 
     * @var \Symfony\Component\Security\Acl\Model\MutableAclProviderInterface
     */
    private $aclProvider;

    /**
     * @var \Symfony\Component\Security\Acl\Permission\PermissionMapInterface
     */
    private $permissionMap;

    /**
     * @var array
     */
    private $permissions;

    /**
     * Constructor
     * 
     * @param MutableAclProviderInterface $aclProvider
     * @param PermissionMapInterface $permissionMap
     */
    public function __construct(MutableAclProviderInterface $aclProvider, PermissionMapInterface $permissionMap)
    {
        $this->aclProvider = $aclProvider;
        $this->permissionMap = $permissionMap;
    }

    /**
     * @see \Symfony\Component\Security\Acl\Model\AclProviderInterface::findAcl
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = array())
    {
        return $this->aclProvider->findAcl($oid, $sids);
    }
    
    /**
     * Returns the mask relative to ObjectIdentity and RoleSecurityIdentity
     * 
     * @param ObjectIdentity $oid
     * @param RoleSecurityIdentity $rid
     * 
     * @return number
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
     * Sets the mask for given ObjectIdentity and RoleSecurityIdentity
     * 
     * @param ObjectIdentity $oid
     * @param RoleSecurityIdentity $rid
     * @param number $mask
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
     * Returns permission map
     * 
     * @return \Symfony\Component\Security\Acl\Permission\PermissionMapInterface
     */
    public function getPermissionMap()
    {
        return $this->permissionMap;
    }
    
    /**
     * Returns permissions list
     * 
     * @return array
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
     * Returns permission masks
     * 
     * @param string $permission
     * 
     * @return array
     */
    public function getPermissionMasks($permission)
    {
        return $this->permissionMap->getMasks(strtoupper($permission), null);
    }
}