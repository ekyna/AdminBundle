<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Extension\AbstractExtension;
use Ekyna\Bundle\AdminBundle\Show\Type\TypeInterface;
use Psr\Container\ContainerInterface;

/**
 * Class DependencyInjectionExtension
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DependencyInjectionExtension extends AbstractExtension
{
    private ContainerInterface $locator;

    /**
     * Constructor.
     *
     * @param ContainerInterface $locator
     */
    public function __construct(ContainerInterface $locator)
    {
        $this->locator = $locator;
    }

    /**
     * @inheritDoc
     */
    public function hasType(string $name): bool
    {
        return $this->locator->has($name);
    }

    /**
     * @inheritDoc
     */
    protected function loadType(string $name): TypeInterface
    {
        if (!$this->hasType($name)) {
            throw new InvalidArgumentException("No show type registered under the name '$name'.");
        }

        return $this->locator->get($name);
    }
}
