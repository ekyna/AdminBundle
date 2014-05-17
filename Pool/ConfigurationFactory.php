<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

/**
 * ConfigurationFactory
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationFactory
{
    /**
     * Creates and register a configuration
     * 
     * @param string $prefix
     * @param string $resourceName
     * @param string $resourceClass
     * @param string $templateNamespace
     * @param string $parentId
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration
     */
    public function createConfiguration($prefix, $resourceName, $resourceClass, $templateNamespace, $parentId = null)
    {
        return new Configuration(
            $prefix,
            $resourceName,
            $resourceClass,
            $templateNamespace,
            $parentId
        );
    }
}
