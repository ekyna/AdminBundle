<?php

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepositoryInterface;

/**
 * Class UserRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UserRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds one user with the given email.
     *
     * @param string $email  The user's email address
     * @param bool   $active Whether to filter active users
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\UserInterface|null
     */
    public function findOneByEmail(string $email, bool $active = true);

    /**
     * Finds the users having the given role.
     *
     * @param string $role   The user's role
     * @param bool   $active Whether to filter active users
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\UserInterface[]
     */
    public function findByRole(string $role, bool $active = true);

    /**
     * Finds the users having at least one of the given roles.
     *
     * @param array $roles  The user's roles
     * @param bool  $active Whether to filter active users
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\userInterface[]
     */
    public function findByRoles(array $roles, bool $active = true);

    /**
     * Returns all active users.
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\userInterface[]
     */
    public function findAllActive();
}
