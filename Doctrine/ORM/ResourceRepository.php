<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseRepository;

/**
 * Class ResourceRepository
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository extends BaseRepository implements ResourceRepositoryInterface
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
}
