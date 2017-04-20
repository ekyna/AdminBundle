<?php

namespace Ekyna\Bundle\AdminBundle\Service\Fixtures;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Bundle\AdminBundle\Repository\GroupRepositoryInterface;
use Exception;

/**
 * Class AdminProvider
 * @package Ekyna\Bundle\AdminBundle\Service\Fixtures
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminProvider
{
    private GroupRepositoryInterface $repository;


    /**
     * Constructor.
     *
     * @param GroupRepositoryInterface $repository
     */
    public function __construct(GroupRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Returns the admin group by its name.
     *
     * @param string $name
     *
     * @return GroupInterface
     *
     * @throws Exception
     */
    public function adminGroup(string $name): GroupInterface
    {
        if ($group = $this->repository->findOneByName($name)) {
            return $group;
        }

        throw new Exception("Group '$name' not found.");
    }
}
