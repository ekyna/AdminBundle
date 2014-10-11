<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\Security\Acl\Domain\ObjectIdentity;

/**
 * Class Configuration
 * @package Ekyna\Bundle\AdminBundle\Pool
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class Configuration implements ConfigurationInterface
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
    protected $eventClass;

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
     * @param string $eventClass        The event FQCN
     * @param string $parentId          The parent configuration identifier
     */
    public function __construct($prefix, $resourceName, $resourceClass, $templateNamespace, $eventClass = null, $parentId = null)
    {
        // Required
        $this->prefix = $prefix;
        $this->resourceName = $resourceName;
        $this->resourceClass = $resourceClass;
        $this->templateNamespace = $templateNamespace;
        // Optional
        $this->eventClass = $eventClass;
        $this->parentId = $parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return sprintf('%s.%s', $this->prefix, $this->resourceName);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrefix()
    {
        return $this->prefix;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentId()
    {
        return $this->parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getParentControllerId()
    {
        return sprintf('%s.controller', $this->parentId);
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceClass()
    {
        return $this->resourceClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getEventClass()
    {
        return $this->eventClass;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceName($plural = false)
    {
        return $plural ? Inflector::pluralize($this->resourceName) : $this->resourceName;
    }

    /**
     * {@inheritdoc}
     */
    public function getResourceLabel($plural = false)
    {
        return sprintf('%s.%s.label.%s', $this->prefix, $this->resourceName, $plural ? 'plural' : 'singular');
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        return sprintf('%s:%s.twig', $this->templateNamespace, $name);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute($action)
    {
        return sprintf('%s_%s_admin_%s', $this->prefix, $this->resourceName, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName($action)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * {@inheritdoc}
     */
    public function getTableType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceName);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceKey($service)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity()
    {
        return new ObjectIdentity(sprintf('%s_%s', $this->prefix, $this->resourceName), $this->resourceClass);
    }

    /**
     * {@inheritdoc}
     */
    public function isRelevant($object)
    {
        return $object instanceOf $this->resourceClass;
    }
}
