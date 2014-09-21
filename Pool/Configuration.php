<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Class Configuration
 * @package Ekyna\Bundle\AdminBundle\Pool
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration
{
    /**
     * @var string
     */
    protected $prefix;

    /**
     * @var string
     */
    protected $resourceName;

    /**
     * @var string
     */
    protected $resourceClass;

    /**
     * @var string
     */
    protected $templateNamespace;

    /**
     * @var string
     */
    protected $parentId;

    /**
     * Constructor.
     * 
     * @param string $prefix            The configuration prefix
     * @param string $resourceName      The resource name
     * @param string $resourceClass     The resource FQCN
     * @param string $templateNamespace The template namespace
     * @param string $parentId          The parent configuration identifier
     */
    public function __construct($prefix, $resourceName, $resourceClass, $templateNamespace = null, $parentId = null)
    {
        $this->prefix = $prefix;
        $this->resourceName = $resourceName;
        $this->resourceClass = $resourceClass;
        $this->templateNamespace = $templateNamespace;
        $this->parentId = $parentId;
    }

    /**
     * Returns the configuration identifier.
     * 
     * @return string
     */
    public function getId()
    {
        return sprintf('%s.%s', $this->prefix, $this->resourceName);
    }

    /**
     * Returns the configuration alias.
     * 
     * @return string
     */
    public function getAlias()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * Returns the prefix.
     * 
     * @return string
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * Returns the parent resource identifier.
     * 
     * @return string
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * Returns the parent controller identifier.
     * 
     * @return string
     */
    public function getParentControllerId()
    {
        return sprintf('%s.controller', $this->parentId);
    }

    /**
     * Returns the resource FQCN.
     * 
     * @return string
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * Returns the resource name.
     *
     * @param boolean $plural
     * @return string
     */
    public function getResourceName($plural = false)
    {
        return $plural ? Inflector::pluralize($this->resourceName) : $this->resourceName;
    }

    /**
     * Returns the resource label.
     * 
     * @param boolean $plural
     * 
     * @return string
     */
    public function getResourceLabel($plural = false)
    {
        return sprintf('%s.%s.label.%s', $this->prefix, $this->resourceName, $plural ? 'plural' : 'singular');
    }

    /**
     * Returns a full qualified template name.
     *  
     * @param string $name
     * 
     * @return string
     */
    public function getTemplate($name)
    {
        return sprintf('%s:%s.twig', $this->templateNamespace, $name);
    }

    /**
     * Returns a full qualified route name for the given action.
     * 
     * @param string $action
     * @return string
     */
    public function getRoute($action)
    {
        return sprintf('%s_%s_admin_%s', $this->prefix, $this->resourceName, $action);
    }

    /**
     * Returns the resource event name for the given action.
     *
     * @param $action
     * @return string
     */
    public function getEventName($action)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $action);
    }

    /**
     * Returns the form type service identifier.
     * 
     * @return string
     */
    public function getFormType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * Returns the table type service identifier.
     * 
     * @return string
     */
    public function getTableType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * Returns a service identifier.
     * 
     * @param string $service
     * 
     * @return string
     */
    public function getServiceKey($service)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $service);
    }

    /**
     * Returns the object (resource) identify
     * 
     * @return \Symfony\Component\Security\Acl\Domain\ObjectIdentity
     */
    public function getObjectIdentity()
    {
        return new ObjectIdentity(sprintf('%s_%s', $this->prefix, $this->resourceName), $this->resourceClass);
    }

    /**
     * Returns whether this configuration is relevant for the given object.
     * 
     * @param object $object
     * 
     * @return boolean
     */
    public function isRelevant($object)
    {
        return $object instanceOf $this->resourceClass;
    }
}
