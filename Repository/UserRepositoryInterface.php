<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\User\Repository\UserRepositoryInterface as BaseRepository;

/**
 * Class UserRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method UserInterface|null find(int $id)
 * @method UserInterface|null findOneBy(array $criteria, array $sorting = [])
 * @method UserInterface[] findAll()
 * @method UserInterface[] findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null)
 */
interface UserRepositoryInterface extends BaseRepository
{
    /**
     * Finds one user by api token.
     *
     * @param string $token   The user's api token
     * @param bool   $enabled Whether to filter enabled users
     *
     * @return UserInterface|null
     */
    public function findOneByApiToken(string $token, bool $enabled = true): ?UserInterface;

    /**
     * Finds the users having the given role.
     *
     * @param string $role    The user's role
     * @param bool   $enabled Whether to filter enabled users
     *
     * @return UserInterface[]
     */
    public function findByRole(string $role, bool $enabled = true): array;

    /**
     * Finds the users having at least one of the given roles.
     *
     * @param array $roles   The user's roles
     * @param bool  $enabled Whether to filter enabled users
     *
     * @return UserInterface[]
     */
    public function findByRoles(array $roles, bool $enabled = true): array;

    /**
     * Returns all enabled users.
     *
     * @return UserInterface[]
     */
    public function findAllActive(): array;
}
