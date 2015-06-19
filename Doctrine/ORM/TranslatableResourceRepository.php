<?php

namespace Ekyna\Bundle\AdminBundle\Doctrine\ORM;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\AdminBundle\Doctrine\ORM\Util\TranslatableResourceRepositoryTrait;

/**
 * Class TranslatableResourceRepository
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class TranslatableResourceRepository extends EntityRepository implements TranslatableResourceRepositoryInterface
{
    use TranslatableResourceRepositoryTrait;
}
