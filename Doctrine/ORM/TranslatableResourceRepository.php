<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util\TranslatableResourceRepositoryTrait;

/**
 * Class TranslatableResourceRepository
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableResourceRepository extends ResourceRepository implements TranslatableResourceRepositoryInterface
{
    use TranslatableResourceRepositoryTrait;
}
