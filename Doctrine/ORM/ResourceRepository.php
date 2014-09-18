<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository as BaseRepository;

/**
 * ResourceRepository
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceRepository extends BaseRepository
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
