<?php

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class GroupRepository
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupRepository extends ResourceRepository implements GroupRepositoryInterface
{
    /**
     * @inheritdoc
     */
    public function findOneByRole($role)
    {
        $this->validateRole($role);

        $qb = $this->createQueryBuilder('g');

        return $qb
            ->andWhere($qb->expr()->like('g.roles', $qb->expr()->literal('%"' . strtoupper($role) . '"%')))
            ->orderBy('g.position', 'DESC')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findByRole($role)
    {
        $this->validateRole($role);

        $qb = $this->createQueryBuilder('g');

        return $qb
            ->andWhere($qb->expr()->like('g.roles', $qb->expr()->literal(strtoupper($role))))
            ->orderBy('g.position', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByRoles(array $roles)
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->createQueryBuilder('g');
        $orRoles = $qb->expr()->orX();
        foreach ($roles as $role) {
            $this->validateRole($role);

            $orRoles->add($qb->expr()->like('g.roles', $qb->expr()->literal('%"' . strtoupper($role) . '"%')));
        }

        return $qb
            ->andWhere($orRoles)
            ->orderBy('g.position', 'DESC')
            ->getQuery()
            ->getResult();
    }

    /**
     * Validates the given role.
     *
     * @param string $role
     *
     * @throws \InvalidArgumentException
     */
    private function validateRole($role)
    {
        if (!preg_match('~^ROLE_([A-Z_]+)~', $role)) {
            throw new \InvalidArgumentException("Role must start with 'ROLE_'.");
        }
    }
}
