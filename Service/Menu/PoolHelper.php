<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Menu;

use Symfony\Component\DependencyInjection\Definition;

/**
 * Class PoolHelper
 * @package Ekyna\Bundle\AdminBundle\Service\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class PoolHelper
{
    private Definition $pool;
    private ?string    $group = null;

    public function __construct(Definition $pool)
    {
        $this->pool = $pool;
    }

    public function addGroup(array $group): self
    {
        $this->pool->addMethodCall('createGroup', [$group]);

        $this->group = $group['name'];

        return $this;
    }

    public function addEntry(array $entry): self
    {
        $this->pool->addMethodCall('createEntry', [$this->group, $entry]);

        return $this;
    }
}
