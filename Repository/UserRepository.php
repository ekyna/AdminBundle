<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Repository;

use DateTime;
use Doctrine\DBAL\Types\Types;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\User\Repository\UserRepository as BaseRepository;
use InvalidArgumentException;

/**
 * Class UserRepository
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserRepository extends BaseRepository implements UserRepositoryInterface
{
    public function findWithEmailConfig(string $email): ?UserInterface
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->andWhere($qb->expr()->eq('u.email', ':email'))
            ->andWhere($qb->expr()->isNotNull('u.emailConfig'))
            ->getQuery()
            ->setParameter('email', $email)
            ->getOneOrNullResult();
    }

    public function findOneByApiToken(string $token, bool $enabled = true): ?UserInterface
    {
        $qb = $this->createQueryBuilder('u');

        $parameters = [
            'token' => $token,
        ];

        if ($enabled) {
            $qb->andWhere($qb->expr()->eq('u.enabled', ':enabled'));
            $parameters['enabled'] = true;
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        return $qb
            ->andWhere($qb->expr()->eq('u.apiToken', ':token'))
            ->andWhere($qb->expr()->gte('u.apiExpiresAt', ':now'))
            ->getQuery()
            ->setParameters($parameters)
            ->setParameter('now', new DateTime(), Types::DATETIME_MUTABLE)
            ->getOneOrNullResult();
    }

    public function findByRole(string $role, bool $enabled = true): array
    {
        $this->validateRole($role);

        $qb = $this->createQueryBuilder('u');

        $parameters = [
            'role' => '%"' . $role . '"%',
        ];

        if ($enabled) {
            $qb->andWhere($qb->expr()->eq('u.enabled', ':enabled'));
            $parameters['enabled'] = true;
        }

        return $qb
            ->join('u.group', 'g')
            ->andWhere($qb->expr()->like('g.roles', ':role'))
            ->getQuery()
            ->setParameters($parameters)
            ->getResult();
    }

    public function findByRoles(array $roles, bool $enabled = true): array
    {
        if (empty($roles)) {
            return [];
        }

        $qb = $this->createQueryBuilder('u');
        $qb->join('u.group', 'g');

        $parameters = [];

        if ($enabled) {
            $qb->andWhere($qb->expr()->eq('u.enabled', ':enabled'));
            $parameters['enabled'] = true;
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

    public function findAllActive(): array
    {
        $qb = $this->createQueryBuilder('u');

        return $qb
            ->andWhere($qb->expr()->eq('u.enabled', ':enabled'))
            ->getQuery()
            ->setParameter('enabled', true)
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
