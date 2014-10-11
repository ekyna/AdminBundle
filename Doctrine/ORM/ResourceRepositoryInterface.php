<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

/**
 * Interface ResourceRepositoryInterface
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceRepositoryInterface
{
    /**
     * Returns a new resource instance.
     *
     * @return object
     */
    public function createNew();
}
