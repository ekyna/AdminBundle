<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Pagerfanta\Adapter\ArrayAdapter;
use Pagerfanta\Adapter\DoctrineORMAdapter;
use Pagerfanta\Pagerfanta;

/**
 * Trait ResourceRepositoryTrait
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util
 * @author Étienne Dauvergne <contact@ekyna.com>
 *
 * @method string getClassName()
 * @method QueryBuilder createQueryBuilder($alias)
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
        return $this->collectionResult($this
            ->getCollectionQueryBuilder()
            ->getQuery()
        );
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
     * @param array $criteria
     * @param array $sorting
     * @param int   $limit
     * @param int   $offset
     *
     * @return array
     */
    public function findBy(array $criteria, array $sorting = array(), $limit = null, $offset = null)
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        if (null !== $limit) {
            $queryBuilder->setMaxResults($limit);
        }

        if (null !== $offset) {
            $queryBuilder->setFirstResult($offset);
        }

        $query = $queryBuilder->getQuery();

        if ($limit == 1) {
            return $query->getResult();
        }

        return $this->collectionResult($query);
    }

    /**
     * @param array $criteria
     *
     * @return null|object
     */
    public function findRandomOneBy(array $criteria)
    {
        $queryBuilder = $this->getQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        return $queryBuilder
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }

    /**
     * @param array $criteria
     * @param int   $limit
     *
     * @return array
     */
    public function findRandomBy(array $criteria, $limit)
    {
        $limit = intval($limit);
        if ($limit <= 1) {
            throw new \InvalidArgumentException('Please use `findRandomOneBy()` for single result.');
        }

        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);

        $query = $queryBuilder
            ->addSelect('RAND() as HIDDEN rand')
            ->orderBy('rand')
            ->setMaxResults($limit)
            ->getQuery()
        ;

        return $this->collectionResult($query);
    }

    /**
     * {@inheritdoc}
     */
    public function createPager(array $criteria = array(), array $sorting = array())
    {
        $queryBuilder = $this->getCollectionQueryBuilder();

        $this->applyCriteria($queryBuilder, $criteria);
        $this->applySorting($queryBuilder, $sorting);

        return $this->getPager($queryBuilder);
    }

    /**
     * @param QueryBuilder $queryBuilder
     *
     * @return Pagerfanta
     */
    public function getPager(QueryBuilder $queryBuilder)
    {
        return new Pagerfanta(new DoctrineORMAdapter($queryBuilder, true, false));
    }

    /**
     * @param array $objects
     *
     * @return Pagerfanta
     */
    public function getArrayPager($objects)
    {
        return new Pagerfanta(new ArrayAdapter($objects));
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
     * @return QueryBuilder
     */
    protected function getCollectionQueryBuilder()
    {
        return $this->createQueryBuilder($this->getAlias());
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $criteria
     */
    protected function applyCriteria(QueryBuilder $queryBuilder, array $criteria = array())
    {
        foreach ($criteria as $property => $value) {
            $name = $this->getPropertyName($property);
            if (null === $value) {
                $queryBuilder->andWhere($queryBuilder->expr()->isNull($name));
            } elseif (is_array($value)) {
                $queryBuilder->andWhere($queryBuilder->expr()->in($name, $value));
            } elseif ('' !== $value) {
                $parameter = str_replace('.', '_', $property);
                $queryBuilder
                    ->andWhere($queryBuilder->expr()->eq($name, ':'.$parameter))
                    ->setParameter($parameter, $value)
                ;
            }
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param array        $sorting
     */
    protected function applySorting(QueryBuilder $queryBuilder, array $sorting = array())
    {
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
     * @param Query $query
     * @return array
     */
    protected function collectionResult(Query $query)
    {
        return $query->getResult();
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
