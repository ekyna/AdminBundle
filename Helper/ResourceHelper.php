<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ResourceHelper
 * @package Ekyna\Bundle\AdminBundle\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO move to resource bundle (or component) with new ACL system
 */
class ResourceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface
     */
    private $aclOperator;

    /**
     * @var RouterInterface|\JMS\I18nRoutingBundle\Router\I18nRouter
     */
    private $router;

    /**
     * @var ResourceEventDispatcherInterface
     */
    private $dispatcher;


    /**
     * Constructor.
     *
     * @param EntityManagerInterface           $em
     * @param ConfigurationRegistry            $registry
     * @param AclOperatorInterface             $aclOperator
     * @param RouterInterface                  $router
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManagerInterface $em,
        ConfigurationRegistry $registry,
        AclOperatorInterface $aclOperator,
        RouterInterface $router,
        ResourceEventDispatcherInterface $dispatcher
    ) {
        $this->em = $em;
        $this->registry = $registry;
        $this->aclOperator = $aclOperator;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns the registry.
     *
     * @return ConfigurationRegistry
     */
    public function getRegistry()
    {
        return $this->registry;
    }

    /**
     * Returns the aclOperator.
     *
     * @return AclOperatorInterface
     */
    public function getAclOperator()
    {
        return $this->aclOperator;
    }

    /**
     * Returns the url generator.
     *
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator()
    {
        return $this->router;
    }

    /**
     * Returns whether the user has access granted or not on the given resource for the given action.
     *
     * @param mixed  $resource
     * @param string $action
     *
     * @return boolean
     */
    public function isGranted($resource, $action = 'view')
    {
        return $this->aclOperator->isAccessGranted($resource, $this->getPermission($action));
    }

    /**
     * Generates an admin path for the given resource and action.
     *
     * @param object $resource
     * @param string $action
     * @param array  $parameters
     * @param bool   $absolute
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function generateResourcePath($resource, $action = 'show', array $parameters = [], $absolute = false)
    {
        $configuration = $this->registry->findConfiguration($resource);
        $routeName = $configuration->getRoute($action);

        $route = $this->findRoute($routeName);
        $requirements = $route->getRequirements();

        $accessor = PropertyAccess::createPropertyAccessor();

        $entities = [];
        if (is_object($resource)) {
            $entities[$configuration->getResourceName()] = $resource;
            $current = $resource;
            while (null !== $parentId = $configuration->getParentId()) {
                $parentConfiguration = $this->registry->findConfiguration($parentId);

                $metadata = $this->em->getClassMetadata($configuration->getResourceClass());
                $associations = $metadata->getAssociationsByTargetClass($parentConfiguration->getResourceClass());

                foreach ($associations as $mapping) {
                    if (
                        $mapping['type'] === ClassMetadataInfo::MANY_TO_ONE
                        && $accessor->isReadable($current, $mapping['fieldName'])
                    ) {
                        $current = $accessor->getValue($current, $mapping['fieldName']);
                        $entities[$parentConfiguration->getResourceName()] = $current;
                    }
                }

                $configuration = $parentConfiguration;
            }
        }

        foreach ($entities as $name => $resource) {
            $parameter = $name . 'Id';
            if (array_key_exists($parameter, $requirements) && !isset($parameters[$parameter])) {
                $parameters[$parameter] = $accessor->getValue($resource, 'id');
            }
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->router->generate($routeName, $parameters, $type);
    }

    /**
     * Returns the public url for the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $absolute
     *
     * @return string|null
     */
    public function generatePublicUrl(ResourceInterface $resource, $absolute = false)
    {
        if (null === $event = $this->dispatcher->createResourceEvent($resource, false)) {
            return null;
        }

        $name = $this->dispatcher->getResourceEventName($resource, 'public_url');

        $this->dispatcher->dispatch($name, $event);

        if (!$event->hasData('route')) {
            return null;
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        $parameters = $event->hasData('parameters') ? $event->getData('parameters') : [];

        return $this->router->generate($event->getData('route'), $parameters, $type);
    }

    /**
     * Returns the permission for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public function getPermission($action)
    {
        $action = strtoupper($action);
        if ($action == 'LIST') {
            return 'VIEW';
        } elseif ($action == 'SHOW') {
            return 'VIEW';
        } elseif ($action == 'NEW') {
            return 'CREATE';
        } elseif ($action == 'EDIT') {
            return 'EDIT';
        } elseif ($action == 'REMOVE') {
            return 'DELETE';
        }

        return $action;
    }

    /**
     * Finds the route definition.
     *
     * @param string $routeName
     * @param bool   $throw
     *
     * @return null|\Symfony\Component\Routing\Route
     */
    public function findRoute($routeName, $throw = true)
    {
        // TODO create a route finder ? (same in CmsBundle BreadcrumbBuilder)
        $i18nRouterClass = 'JMS\I18nRoutingBundle\Router\I18nRouterInterface';
        if (interface_exists($i18nRouterClass) && $this->router instanceof $i18nRouterClass) {
            $route = $this->router->getOriginalRouteCollection()->get($routeName);
        } else {
            $route = $this->router->getRouteCollection()->get($routeName);
        }
        if (null === $route && $throw) {
            throw new \RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }

        return $route;
    }
}
