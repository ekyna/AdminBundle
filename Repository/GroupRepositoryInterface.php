<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Repository\ResourceRepositoryInterface;

/**
 * Interface GroupRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @method GroupInterface|null find(int $id)
 * @method GroupInterface|null findOneBy(array $criteria, array $sorting = [])
 * @method GroupInterface[] findAll()
 * @method GroupInterface[] findBy(array $criteria, array $sorting = [], int $limit = null, int $offset = null)
 */
interface GroupRepositoryInterface extends ResourceRepositoryInterface
{
    /**
     * Finds the group by its name.
     *
     * @param string $name
     *
     * @return GroupInterface|null
     */
    public function findOneByName(string $name): ?GroupInterface;

    /**
     * Finds the lower privilege level group having the given role.
     *
     * @param string $role
     *
     * @return GroupInterface|null
     */
    public function findOneByRole(string $role): ?GroupInterface;

    /**
     * Finds the groups having the given role.
     *
     * @param string $role
     *
     * @return GroupInterface[]
     */
    public function findByRole(string $role): array;

    /**
     * Finds the groups having at least one of the given roles.
     *
     * @param array $roles
     *
     * @return GroupInterface[]
     */
    public function findByRoles(array $roles): array;
}
