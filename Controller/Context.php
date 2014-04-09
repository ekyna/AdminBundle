<?php

namespace Ekyna\Bundle\AdminBundle\Controller;

class Context
{    
    protected $ownerResourceName;
    protected $resources;
    protected $identifiers;

    public function __construct($ownerResourceName)
    {
        $this->ownerResourceName = $ownerResourceName;
        $this->resources = array();
        //$this->identifiers = array();
    }

    public function addResource($name, $resource)
    {
        $this->resources[$name] = $resource;

        return $this;
    }

    public function getResource($name)
    {
        if(isset($this->resources[$name])) {
            return $this->resources[$name];
        }
        return null;
    }

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

    public function getTemplateVars(array $extras = array())
    {
        return array_merge($this->resources, array('identifiers' => $this->getIdentifiers()), $extras);
    }
}
