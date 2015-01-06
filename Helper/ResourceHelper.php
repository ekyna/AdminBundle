<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

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
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
     */
    private $registry;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface
     */
    private $aclOperator;

    /**
     * @var \Symfony\Component\Routing\RouterInterface
     */
    private $router;

    /**
     * Constructor.
     *
     * @param ConfigurationRegistry $registry
     * @param AclOperatorInterface  $aclOperator
     * @param RouterInterface       $router
     */
    public function __construct(
        ConfigurationRegistry $registry,
        AclOperatorInterface $aclOperator,
        RouterInterface $router
    ) {
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
     *
     * @throws \RuntimeException
     *
     * @return string
     */
    public function generateResourcePath($resource, $action = 'show')
    {
        $configuration = $this->registry->findConfiguration($resource);
        $routeName = $configuration->getRoute($action);

        $accessor = PropertyAccess::createPropertyAccessor();
        if (null === $route = $this->router->getRouteCollection()->get($routeName)) {
            throw new \RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }

        $requirements = $route->getRequirements();

        $entities = array();
        if (is_object($resource)) {
            $entities[$configuration->getResourceName()] = $resource;
            $current = $resource;
            while (null !== $configuration->getParentId()) {
                $configuration = $this->registry->findConfiguration($configuration->getParentId());
                $current = $accessor->getValue($current, $configuration->getResourceName());
                $entities[$configuration->getResourceName()] = $current;
            }
        }

        $parameters = array();
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
}
