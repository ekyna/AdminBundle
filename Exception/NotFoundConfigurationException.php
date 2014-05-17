<?php

namespace Ekyna\Bundle\AdminBundle\Exception;

/**
 * NotFoundConfigurationException.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class NotFoundConfigurationException extends \InvalidArgumentException
{
    public function __construct($resource)
    {
        parent::__construct(sprintf('Unable to find configuration for resource "%s".', is_object($resource) ? get_class($resource) : $resource));
	}
}
