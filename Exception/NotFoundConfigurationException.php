<?php

namespace Ekyna\Bundle\AdminBundle\Exception;

/**
 * Class NotFoundConfigurationException
 * @package Ekyna\Bundle\AdminBundle\Exception
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class NotFoundConfigurationException extends \InvalidArgumentException
{
    /**
     * @param string $resource
     */
    public function __construct($resource)
    {
        parent::__construct(sprintf('Unable to find configuration for resource "%s".', is_object($resource) ? get_class($resource) : $resource));
	}
}
