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
    private $resourceId;

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
    protected $templatesList;

    /**
     * @var string
     */
    protected $parentId;

    /**
     * Constructor.
     *
     * @param string $prefix            The configuration prefix
     * @param string $resourceId        The resource id
     * @param string $resourceClass     The resource FQCN
     * @param array  $templatesList     The templates list
     * @param string $eventClass        The event FQCN
     * @param string $parentId          The parent configuration identifier
     */
    public function __construct($prefix, $resourceId, $resourceClass, array $templatesList, $eventClass = null, $parentId = null)
    {
        // Required
        $this->prefix = $prefix;
        $this->resourceId = $resourceId;
        $this->resourceName = Inflector::camelize($resourceId);
        $this->resourceClass = $resourceClass;
        $this->templatesList = $templatesList;

        // Optional
        $this->eventClass = $eventClass;
        $this->parentId = $parentId;
    }

    /**
     * {@inheritdoc}
     */
    public function getId()
    {
        return sprintf('%s.%s', $this->prefix, $this->resourceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getAlias()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceId);
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
        return sprintf('%s.%s.label.%s', $this->prefix, $this->resourceId, $plural ? 'plural' : 'singular');
    }

    /**
     * {@inheritdoc}
     */
    public function getTemplate($name)
    {
        if (array_key_exists($name, $this->templatesList)) {
            return sprintf('%s.twig', $this->templatesList[$name]);
        }
        throw new \InvalidArgumentException(sprintf('Template "%s.twig" is not registered.', $name));
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutePrefix()
    {
        return sprintf('%s_%s_admin', $this->prefix, $this->resourceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getRoute($action)
    {
        return sprintf('%s_%s', $this->getRoutePrefix(), $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getEventName($action)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceId, $action);
    }

    /**
     * {@inheritdoc}
     */
    public function getFormType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getTableType()
    {
        return sprintf('%s_%s', $this->prefix, $this->resourceId);
    }

    /**
     * {@inheritdoc}
     */
    public function getServiceKey($service)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceId, $service);
    }

    /**
     * {@inheritdoc}
     */
    public function getObjectIdentity()
    {
        return new ObjectIdentity($this->getAlias(), $this->resourceClass);
    }

    /**
     * {@inheritdoc}
     */
    public function isRelevant($object)
    {
        $class = $this->resourceClass;
        return $object instanceOf $class;
    }
}
