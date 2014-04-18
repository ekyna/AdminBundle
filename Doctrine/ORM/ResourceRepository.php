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
    public function createNew()
    {
        $class = $this->getClassName();
        return new $class;
    }
}
