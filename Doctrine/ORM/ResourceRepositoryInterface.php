<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Doctrine\Common\Collections\Selectable;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\QueryBuilder;

/**
 * Interface ResourceRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceRepositoryInterface extends ObjectRepository, Selectable
{
    /**
     * Returns a new resource instance.
     *
     * @return object
     */
    public function createNew();

    /**
     * Finds the resource by his ID.
     *
     * @param int $id
     *
     * @return null|object
     */
    public function find($id);

    /**
     * Finds all resources.
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findAll();

    /**
     * Finds one resource by criteria and sorting.
     *
     * @param array $criteria
     *
     * @return null|object
     */
    public function findOneBy(array $criteria);

    /**
     * Finds resources by criteria, sorting, limit and offset.
     *
     * @param array $criteria
     * @param array $sorting
     * @param int   $limit
     * @param int   $offset
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findBy(array $criteria, array $sorting = array(), $limit = null, $offset = null);

    /**
     * Finds a random resource by criteria.
     *
     * @param array $criteria
     *
     * @return null|object
     */
    public function findRandomOneBy(array $criteria);

    /**
     * Finds random resource by criteria and limit.
     *
     * @param array $criteria
     * @param int   $limit
     *
     * @return array|\Doctrine\ORM\Tools\Pagination\Paginator
     */
    public function findRandomBy(array $criteria, $limit);

    /**
     * Creates a pager.
     *
     * @param array $criteria
     * @param array $sorting
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function createPager(array $criteria = array(), array $sorting = array());

    /**
     * Returns the (doctrine) pager.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return \Pagerfanta\Pagerfanta
     */
    public function getPager(QueryBuilder $queryBuilder);
}
