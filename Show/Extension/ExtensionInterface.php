<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension;

use Ekyna\Bundle\AdminBundle\Show\Type\TypeInterface;

/**
 * Interface ExtensionInterface
 * @package Ekyna\Bundle\AdminBundle\Show\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ExtensionInterface
{
    /**
     * Returns whether this extension has the type for the given name.
     *
     * @param string $name
     *
     * @return bool
     */
    public function hasType(string $name): bool;

    /**
     * Returns the type for the given name.
     *
     * @param string $name
     *
     * @return TypeInterface
     */
    public function getType(string $name): TypeInterface;
}
