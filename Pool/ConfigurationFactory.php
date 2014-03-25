<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

/**
 * ConfigurationFactory
 */
class ConfigurationFactory
{
    /**
     * Creates and register a configuration
     * @param string $id
     * @param string $resourceClass
     * @param string $resourceName
     * @param string $templateNamespace
     * @param string $parentId
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration
     */
    public function createConfiguration($id, $resourceClass, $resourceName, $templateNamespace, $parentId = null)
    {
        return new Configuration(
            $id,
            $resourceClass,
            $resourceName,
            $templateNamespace,
            $parentId
        );
    }
}
