<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

/**
 * ConfigurationRegistry
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationRegistry
{
    protected $configurations;

    public function __construct(array $configurations)
    {
        $this->configurations = $configurations;
    }

    public function has($id)
    {
        return isset($this->configurations[$id]);
    }

    public function get($id)
    {
        if(!$this->has($id)) {
            throw new \InvalidArgumentException(sprintf('Configuration "%s" not found.', $id));
        }
        return $this->configurations[$id];
    }

    public function getConfigurations()
    {
        return $this->configurations;
    }

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
