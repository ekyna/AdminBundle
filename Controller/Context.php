<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

/**
 * Class Context
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Context
{
    /**
     * @var string
     */
    protected $ownerResourceName;

    /**
     * @var array
     */
    protected $resources;

    /**
     * Constructor.
     *
     * @param string $ownerResourceName
     */
    public function __construct($ownerResourceName)
    {
        $this->ownerResourceName = $ownerResourceName;
        $this->resources = array();
    }

    /**
     * Adds the resource.
     *
     * @param string $name
     * @param object $resource
     * @return Context
     */
    public function addResource($name, $resource)
    {
        $this->resources[$name] = $resource;

        return $this;
    }

    /**
     * Returns a resource by name.
     *
     * @param $name
     * @return object|null
     */
    public function getResource($name)
    {
        if(isset($this->resources[$name])) {
            return $this->resources[$name];
        }
        return null;
    }

    /**
     * Returns the identifiers.
     *
     * @param bool $with_current
     * @return array
     */
    public function getIdentifiers($with_current = false)
    {
        $identifiers = array();
        foreach($this->resources as $name => $resource) {
            if(!(!$with_current && $name === $this->ownerResourceName)) {
                $identifiers[$name.'Id'] = $resource->getId();
            }
        }
        return $identifiers;
    }

    /**
     * Returns the template resources vars.
     *
     * @param array $extras
     * @return array
     */
    public function getTemplateVars(array $extras = array())
    {
        return array_merge($this->resources, array('identifiers' => $this->getIdentifiers()), $extras);
    }
}
