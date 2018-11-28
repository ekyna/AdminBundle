<?php

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Interface GroupRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GroupRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the lower privilege level group having the given role.
     *
     * @param string $role
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\GroupInterface|null
     */
    public function findOneByRole($role);

    /**
     * Finds the groups having the given role.
     *
     * @param string $role
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\GroupInterface[]
     */
    public function findByRole($role);

    /**
     * Finds the groups having at least one of the given roles.
     *
     * @param array $roles
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\GroupInterface[]
     */
    public function findByRoles(array $roles);
}
