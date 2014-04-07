<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

/**
 * ConfigurationFactory
 */
class ConfigurationFactory
{
    /**
     * Creates and register a configuration
     * 
     * @param string $id
     * @param string $resourceName
     * @param string $resourceClass
     * @param string $templateNamespace
     * @param string $parentId
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration
     */
    public function createConfiguration($id, $resourceName, $resourceClass, $templateNamespace, $parentId = null)
    {
        return new Configuration(
            $id,
            $resourceName,
            $resourceClass,
            $templateNamespace,
            $parentId
        );
    }
}
