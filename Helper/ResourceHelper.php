<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

use Doctrine\Common\Persistence\Mapping\ClassMetadataFactory;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\RouterInterface;

/**
 * Class ResourceHelper
 * @package Ekyna\Bundle\AdminBundle\Helper
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $em;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
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
     * Constructor.
     *
     * @param EntityManagerInterface $em
     * @param ConfigurationRegistry $registry
     * @param AclOperatorInterface  $aclOperator
     * @param RouterInterface       $router
     */
    public function __construct(
        EntityManagerInterface $em,
        ConfigurationRegistry $registry,
        AclOperatorInterface $aclOperator,
        RouterInterface $router
    ) {
        $this->em = $em;
        $this->registry = $registry;
        $this->aclOperator = $aclOperator;
        $this->router = $router;
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
     * Returns the router.
     *
     * @return RouterInterface
     */
    public function getRouter()
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
     * @param array $parameters
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function generateResourcePath($resource, $action = 'show', array $parameters = [])
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
            while (null !== $configuration->getParentId()) {
                $parentConfiguration = $this->registry->findConfiguration($configuration->getParentId());

                $metadata = $this->em->getClassMetadata($configuration->getResourceClass());
                $associations = $metadata->getAssociationsByTargetClass($parentConfiguration->getResourceClass());

                foreach ($associations as $mapping) {
                    if ($mapping['type'] === ClassMetadataInfo::MANY_TO_ONE) {
                        $current = $accessor->getValue($current, $mapping['fieldName']);
                        $entities[$parentConfiguration->getResourceName()] = $current;
                    }
                }

                $configuration = $parentConfiguration;
            }
        }

        foreach ($entities as $name => $resource) {
            if (array_key_exists($name . 'Id', $requirements)) {
                $parameters[$name . 'Id'] = $accessor->getValue($resource, 'id');
            }
        }

        return $this->router->generate($routeName, $parameters);
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
     * @return null|\Symfony\Component\Routing\Route
     */
    public function findRoute($routeName)
    {
        // TODO create a route finder ? (same in CmsBundle BreadcrumbBuilder)
        $i18nRouterClass = 'JMS\I18nRoutingBundle\Router\I18nRouterInterface';
        if (interface_exists($i18nRouterClass) && $this->router instanceof $i18nRouterClass) {
            $route = $this->router->getOriginalRouteCollection()->get($routeName);
        } else {
            $route = $this->router->getRouteCollection()->get($routeName);
        }
        if (null === $route) {
            throw new \RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }
        return $route;
    }
}
