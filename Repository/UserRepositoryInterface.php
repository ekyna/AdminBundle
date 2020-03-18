<?php

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
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
     * @return UserInterface|null
     */
    public function findOneByEmail(string $email, bool $active = true): ?UserInterface;

    /**
     * Finds the users having the given role.
     *
     * @param string $role   The user's role
     * @param bool   $active Whether to filter active users
     *
     * @return UserInterface[]
     */
    public function findByRole(string $role, bool $active = true): array;

    /**
     * Finds the users having at least one of the given roles.
     *
     * @param array $roles  The user's roles
     * @param bool  $active Whether to filter active users
     *
     * @return UserInterface[]
     */
    public function findByRoles(array $roles, bool $active = true): array;

    /**
     * Finds one user by api token.
     *
     * @param string $token The user's api token
     * @param bool  $active Whether to filter active users
     *
     * @return UserInterface|null
     */
    public function findOneByApiToken(string $token, bool $active = true): ?UserInterface;

    /**
     * Returns all active users.
     *
     * @return UserInterface[]
     */
    public function findAllActive(): array;
}
