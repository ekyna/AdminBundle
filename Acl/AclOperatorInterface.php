<?php

namespace Ekyna\Bundle\AdminBundle\Acl;

use Ekyna\Bundle\UserBundle\Model\GroupInterface;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;
use Symfony\Component\Security\Acl\Domain\RoleSecurityIdentity;
use Symfony\Component\Security\Acl\Model\ObjectIdentityInterface;

/**
 * AclOperatorInterface.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface AclOperatorInterface
{
    /**
     * @see \Symfony\Component\Security\Acl\Model\AclProviderInterface::findAcl
     */
    public function findAcl(ObjectIdentityInterface $oid, array $sids = array());

    /**
     * Returns the mask relative to ObjectIdentity and RoleSecurityIdentity
     *
     * @param ObjectIdentity $oid
     * @param RoleSecurityIdentity $rid
     *
     * @return number
     */
    public function getClassMask(ObjectIdentity $oid, RoleSecurityIdentity $rid);

    /**
     * Sets the mask for given ObjectIdentity and RoleSecurityIdentity
     *
     * @param ObjectIdentity $oid
     * @param RoleSecurityIdentity $rid
     * @param number $mask
     */
    public function setClassMask(ObjectIdentity $oid, RoleSecurityIdentity $rid, $mask = 0);

    /**
     * Returns permission map
     *
     * @return \Symfony\Component\Security\Acl\Permission\PermissionMapInterface
     */
    public function getPermissionMap();

    /**
     * Returns permissions list
     *
     * @return array
     */
    public function getPermissions();

    /**
     * Returns permission masks
     *
     * @param string $permission
     *
     * @return array
     */
    public function getPermissionMasks($permission);

    /**
     * Builds the group permissions form.
     * 
     * @param \Symfony\Component\Form\FormBuilderInterface $builder
     */
    public function buildGroupForm(FormBuilderInterface $builder);

    /**
     * Generates the group permissions form datas.
     * 
     * @param \Ekyna\Bundle\UserBundle\Model\GroupInterface $group
     */
    public function generateGroupFormDatas(GroupInterface $group);

    /**
     * Generates the group permissions view datas.
     * 
     * @param \Ekyna\Bundle\UserBundle\Model\GroupInterface $group
     */
    public function generateGroupViewDatas(GroupInterface $group);

    /**
     * Updates the group permissions from the given datas.
     * 
     * @param \Ekyna\Bundle\UserBundle\Model\GroupInterface $group
     * @param array                                         $datas
     */
    public function updateGroup(GroupInterface $group, array $datas);

    /**
     * Returns whether the current user is granted for the given permission on the given resource.
     *
     * @param mixed  $resource
     * @param string $permission
     *
     * @throws \InvalidArgumentException
     *
     * @return boolean
     */
    public function isAccessGranted($resource, $permission);
}
