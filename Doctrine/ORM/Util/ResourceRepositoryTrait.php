<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util;

use Doctrine\ORM\QueryBuilder;

/**
 * Trait ResourceRepositoryTrait
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
trait ResourceRepositoryTrait
{
    /**
     * Creates a new resource.
     *
     * @return mixed
     */
    public function createNew()
    {
        $class = $this->getClassName();
        return new $class;
    }

    /**
     * @param mixed $id
     *
     * @return null|object
     */
    public function find($id)
    {
        return $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias().'.id = '.intval($id))
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @return array
     */
    public function findAll()
    {
        return $this
            ->getQueryBuilder()
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * @param array $criteria
     *
     * @return null|object
     */
    public function findOneBy(array $criteria)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param array   $criteria
     * @param array   $orderBy
     * @param integer $limit
     * @param integer $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $orderBy);

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        return $queryBuilder
            ->getQuery()
            ->getResult()
        ;
    }

    /**
     * Returns the query builder.
     *
     * @return QueryBuilder
     */
    protected function getQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @param array $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = null)
    {
        if (null === $criteria) {
            return;
        }

        foreach ($criteria as $property => $value) {
            if (null === $value) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->isNull($this->getPropertyName($property)));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($this->getPropertyName($property), $value));
            } elseif ('' !== $value) {
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq(
                        $this->getPropertyName($property),
                        ':' . $key = str_replace('.', '_', $property))
                    )
                    ->setParameter($key, $value)
                ;
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @param array $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = null)
    {
        if (null === $sorting) {
            return;
        }

        foreach ($sorting as $property => $order) {
            if (!empty($order)) {
                $queryBuilder->addOrderBy($this->getPropertyName($property), $order);
            }
        }
    }

    /**
     * @param string $name
     *
     * @return string
     */
    protected function getPropertyName($name)
    {
        if (false === strpos($name, '.')) {
            return $this->getAlias().'.'.$name;
        }

        return $name;
    }

    /**
     * Returns the alias.
     *
     * @return string
     */
    protected function getAlias()
    {
        return 'o';
    }
}