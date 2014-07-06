<?php

namespace Ekyna\Bundle\AdminBundle\Event;

use Symfony\Component\EventDispatcher\Event;

/**
 * ResourceEvent.
 *
 * @author Etienne Dauvergne <contact@ekyna.com>
 */
class ResourceEvent extends Event
{
    /**
     * @var mixed
     */
    protected $resource;

    /**
     * Sets the resource.
     * 
     * @param mixed $resource
     */
    public function setResource($resource)
    {
        $this->resource = $resource;
    }

    /**
     * Returns the resource.
     * 
     * @return mixed
     */
    public function getResource()
    {
        return $this->resource;
    }
}
