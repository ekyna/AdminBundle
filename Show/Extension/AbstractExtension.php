<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\TypeInterface;

/**
 * Class AbstractExtension
 * @package Ekyna\Bundle\AdminBundle\Show\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExtension implements ExtensionInterface
{
    /**
     * @var TypeInterface[]
     */
    private array $types = [];


    /**
     * @inheritDoc
     */
    public function getType(string $name): TypeInterface
    {
        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        if (null !== $type = $this->loadType($name)) {
            return $this->types[$name] = $type;
        }

        throw new InvalidArgumentException("Unsupported type '$name'.");
    }

    /**
     * Loads the type by it's name.
     *
     * @param string $name
     *
     * @return TypeInterface
     */
    abstract protected function loadType(string $name): TypeInterface;
}
