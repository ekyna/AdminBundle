<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Ekyna\Bundle\AdminBundle\Exception\NotFoundConfigurationException;

/**
 * ConfigurationRegistry
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationRegistry
{
    /**
     * @var array
     */
    protected $configurations;

    /**
     * Constructor.
     * 
     * @param array $configurations
     */
    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    /**
     * Finds a configuration for the given resource (object/class/id)
     * 
     * @param mixed $resource
     * 
     * @throws \Ekyna\Bundle\AdminBundle\Exception\NotFoundConfigurationException
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration|NULL
     */
    public function findConfiguration($resource, $throwException = true)
    {
        // By object
        if (is_object($resource)) {
            foreach ($this->configurations as $config) {
                if ($config->isRelevant($resource)) {
                    return $config;
                }
            }
        // By class
        } elseif (class_exists($resource, false)) {
            foreach ($this->configurations as $config) {
                if ($resource == $config->getResourceClass()) {
                    return $config;
                }
            }
        // By configuration identifier
        } elseif (is_string($resource)) {
            // By Alias
            if ($this->has($resource)) {
                return $this->get($resource);
            }
            // By Id
            foreach($this->configurations as $config) {
                if ($resource == $config->getId()) {
                    return $config;
                }
            }
        }

        if ($throwException) {
            throw new NotFoundConfigurationException($resource);
        }

        return null;
    }

    /**
     * Returns whether a configuration exists or not for the given identifier.
     *  
     * @param string $id
     * 
     * @return boolean
     */
    public function has($id)
    {
        return array_key_exists($id, $this->configurations);
    }

    /**
     * Returns the configuration for the given identifier.
     * 
     * @param string $id
     * 
     * @throws \InvalidArgumentException
     */
    public function get($id)
    {
        if(!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Configuration "%s" not found.', $id));
        }
        return $this->configurations[$id];
    }

    /**
     * Returns all the ancestors configuration.
     *
     * @param Configuration $configuration
     * @param bool          $included
     *
     * @return array
     */
    public function getAncestors(Configuration $configuration, $included = false)
    {
        $ancestors = array();
        if ($included) {
            $ancestors[$configuration->getResourceName()] = $configuration;
        }

        while (null !== $configuration->getParentId()) {
            $configuration = $this->registry->findConfiguration($configuration->getParentId());
            $ancestors[$configuration->getResourceName()] = $configuration;
        }

        return array_reverse($ancestors);
    }

    /**
     * Returns the configurations.
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration[]
     */
    public function getConfigurations()
    {
        return $this->configurations;
    }

    /**
     * Returns the object identity.
     * 
     * @param object $object
     * 
     * @return Symfony\Component\Security\Acl\Domain\ObjectIdentity|NULL
     */
    public function getObjectIdentity($object)
    {
        foreach($this->configurations as $config) {
            if($config->isRelevant($object)) {
                return $config->getObjectIdentity();
            }
        }
        return null;
    }
}
