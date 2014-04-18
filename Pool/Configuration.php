<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Configuration
 *
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
     * Constructor
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

    public function getId()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    public function getPrefix()
    {
        return $this->prefix;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function getParentControllerId()
    {
        return sprintf('%s.controller', $this->parentId);
    }

    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    public function getResourceName()
    {
        return $this->resourceName;
    }

    public function getResourceLabel($plural = false)
    {
        return sprintf('%s.%s.label.%s', $this->prefix, $this->resourceName, $plural ? 'plural' : 'singular');
    }

    public function getTemplate($name)
    {
        return sprintf('%s:%s.twig', $this->templateNamespace, $name);
    }

    public function getRoute($action)
    {
        return sprintf('%s_%s_admin_%s', $this->prefix, $this->resourceName, $action);
    }

    public function getFormType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    public function getTableType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    public function getServiceKey($service)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $service);
    }

    public function getObjectIdentity()
    {
        return new ObjectIdentity(sprintf('%s_%s', $this->prefix, $this->resourceName), $this->resourceClass);
    }

    public function isRelevant($object)
    {
        return $object instanceOf $this->resourceClass;
    }
}
