<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

/**
 * Class ConfigurationFactory
 * @package Ekyna\Bundle\AdminBundle\Pool
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
     * @param array  $templateList
     * @param string $eventClass
     * @param string $parentId
     * 
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration
     */
    public function createConfiguration($prefix, $resourceName, $resourceClass, array $templateList, $eventClass = null, $parentId = null)
    {
        return new Configuration(
            $prefix,
            $resourceName,
            $resourceClass,
            $templateList,
            $eventClass,
            $parentId
        );
    }
}
