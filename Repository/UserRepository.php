<?php

namespace Ekyna\Bundle\AdminBundle\Repository;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\Resource\Doctrine\ORM\ResourceRepository;

/**
 * Class UserRepository
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserRepository extends ResourceRepository implements UserRepositoryInterface
{
    /**
     * @inheritDoc
     */
    public function findOneByEmail(string $email, bool $active = true): ?UserInterface
    {
        $qb = $this->createQueryBuilder('u');

        $parameters = [
            'email' => $email,
        ];

        if ($active) {
            $qb->andWhere($qb->expr()->eq('u.active', ':active'));
            $parameters['active'] = true;
        }

        return $qb
            ->andWhere($qb->expr()->eq('u.email', ':email'))
            ->getQuery()
            ->setParameters($parameters)
            ->getOneOrNullResult();
    }

    /**
     * @inheritdoc
     */
    public function findByRole(string $role, bool $active = true): array
    {
        $this->validateRole($role);

        $qb = $this->createQueryBuilder('u');

        $parameters = [
            'role' => '%"' . $role . '"%',
        ];

        if ($active) {
            $qb->andWhere($qb->expr()->eq('u.active', ':active'));
            $parameters['active'] = true;
        }

        return $qb
            ->join('u.group', 'g')
            ->andWhere($qb->expr()->like('g.roles', ':role'))
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findByRoles(array $roles, bool $active = true): array
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->createQueryBuilder('u');
        $qb->join('u.group', 'g');

        $parameters = [];

        if ($active) {
            $qb->andWhere($qb->expr()->eq('u.active', ':active'));
            $parameters['active'] = true;
        }

        $count = 0;
        $orRoles = $qb->expr()->orX();
        foreach ($roles as $role) {
            $this->validateRole($role);
            $orRoles->add($qb->expr()->like('g.roles', ':role_' . $count));
            $parameters['role_' . $count] = '%"' . $role . '"%';
            $count++;
        }

        return $qb
            ->andWhere($orRoles)
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    /**
     * @inheritdoc
     */
    public function findAllActive(): array
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->andWhere($qb->expr()->eq('u.active', ':active'))
            ->getQuery()
            ->setParameter('active', true)
            ->getResult();
    }

    /**
     * @inheritDoc
     */
    public function findOneByApiToken(string $token, bool $active = true): ?UserInterface
    {
        $qb = $this->createQueryBuilder('u');

        $parameters = [
            'token' => $token,
        ];

        if ($active) {
            $qb->andWhere($qb->expr()->eq('u.active', ':active'));
            $parameters['active'] = true;
        }

        return $qb
            ->andWhere($qb->expr()->eq('u.apiToken', ':token'))
            ->getQuery()
            ->setParameters($parameters)
            ->getOneOrNullResult();
    }

    /**
     * Validates the given role.
     *
     * @param string $role
     *
     * @throws \InvalidArgumentException
     */
    private function validateRole($role): void
    {
        if (!preg_match('~^ROLE_([A-Z_]+)~', $role)) {
            throw new \InvalidArgumentException("Role must start with 'ROLE_'.");
        }
    }
}
