<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Extension\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Class DependencyInjectionExtension
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class DependencyInjectionExtension extends AbstractExtension
{
    /**
     * @var ContainerInterface
     */
    private $container;

    /**
     * @var array
     */
    private $services = [];


    /**
     * Constructor.
     *
     * @param ContainerInterface $container
     * @param array              $types
     */
    public function __construct(ContainerInterface $container, array $types)
    {
        $this->container = $container;

        foreach ($types as $name => $id) {
            $this->registerType($name, $id);
        }
    }

    /**
     * Register the given type.
     *
     * @param string $name
     * @param string $service
     *
     * @return self
     */
    public function registerType($name, $service)
    {
        if (isset($this->services[$name])) {
            throw new InvalidArgumentException("Type '$name' is already registered.");
        }

        $this->services[$name] = $service;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function hasType($name)
    {
        return isset($this->services[$name]);
    }

    /**
     * @inheritDoc
     */
    protected function loadType($name)
    {
        return $this->container->get($this->services[$name]);
    }
}