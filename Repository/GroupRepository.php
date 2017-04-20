<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Doctrine\ORM\Repository\ResourceRepository;
use InvalidArgumentException;

/**
 * Class GroupRepository
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupRepository extends ResourceRepository implements GroupRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByName(string $name): ?GroupInterface
    {
        $qb = $this->createQueryBuilder('g');

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb
            ->andWhere($qb->expr()->eq('g.name', ':name'))
            ->getQuery()
            ->setParameter('name', $name)
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByRole(string $role): ?GroupInterface
    {
        $this->validateRole($role);

        $qb = $this->createQueryBuilder('g');

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb
            ->andWhere($qb->expr()->like('g.roles', $qb->expr()->literal('%"' . strtoupper($role) . '"%')))
            ->orderBy('g.position', 'DESC')
            ->getQuery()
            ->getOneOrNullResult();
    }

    /**
     * @inheritDoc
     */
    public function findByRole(string $role): array
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
     * @inheritDoc
     */
    public function findByRoles(array $roles): array
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
     * @throws InvalidArgumentException
     */
    private function validateRole(string $role): void
    {
        if (preg_match('~^ROLE_([A-Z_]+)~', $role)) {
            return;
        }

        throw new InvalidArgumentException("Role must start with 'ROLE_'.");
    }
}
