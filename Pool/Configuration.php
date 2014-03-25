<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Configuration
 */
class Configuration
{
    /**
     * @var string
     */
    protected $id;

    /**
     * @var string
     */
    protected $resourceClass;

    /**
     * @var string
     */
    protected $resourceName;

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
     * @param string $id                The configuration identifier
     * @param string $resourceClass     The resource FQCN
     * @param string $resourceName      The resource name
     * @param string $templateNamespace The template namespace
     * @param string $parentId          The parent configuration identifier
     */
    public function __construct($id, $resourceClass, $resourceName, $templateNamespace = null, $parentId = null)
    {
        $this->id = $id;
        $this->resourceClass = $resourceClass;
        $this->resourceName = $resourceName;
        $this->templateNamespace = $templateNamespace;
        $this->parentId = $parentId;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getParentId()
    {
        return $this->parentId;
    }

    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    public function getResourceName()
    {
        return $this->resourceName;
    }

    public function getTemplate($name)
    {
        return sprintf('%s:%s.twig', $this->templateNamespace, $name);
    }

    public function getRoute($action)
    {
        return sprintf('%s_admin_%s', $this->id, $action);
    }

    public function getFormType()
    {
        return $this->id;
    }

    public function getTableType()
    {
        return $this->id;
    }

    public function getServiceKey($service)
    {
        return sprintf('%s.%s', $this->id, $service);
    }
    
    public function getObjectIdentity()
    {
        return new ObjectIdentity($this->id, $this->resourceClass);
    }

    public function isRelevant($object)
    {
        return $object instanceOf $this->resourceClass;
    }
}
