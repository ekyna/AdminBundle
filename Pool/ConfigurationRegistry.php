<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

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
     * Returns the configurations.
     * 
     * @return Configuration[]
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
