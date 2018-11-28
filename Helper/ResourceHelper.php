<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Model\Actions;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class ResourceHelper
 * @package Ekyna\Bundle\AdminBundle\Helper
 * @author  Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO    move to resource bundle (or component)
 */
class ResourceHelper
{
    /**
     * @var EntityManagerInterface
     */
    private $manager;

    /**
     * @var ConfigurationRegistry
     */
    private $registry;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

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
     * @param EntityManagerInterface           $manager
     * @param ConfigurationRegistry            $registry
     * @param AuthorizationCheckerInterface    $authorization
     * @param RouterInterface                  $router
     * @param ResourceEventDispatcherInterface $dispatcher
     */
    public function __construct(
        EntityManagerInterface $manager,
        ConfigurationRegistry $registry,
        AuthorizationCheckerInterface $authorization,
        RouterInterface $router,
        ResourceEventDispatcherInterface $dispatcher
    ) {
        $this->manager = $manager;
        $this->registry = $registry;
        $this->authorization = $authorization;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    /**
     * Returns the em.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager()
    {
        return $this->manager;
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
        return $this->authorization->isGranted($this->getPermission($action), $resource);
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

                $metadata = $this->manager->getClassMetadata($configuration->getResourceClass());
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
     * @param string            $locale
     *
     * @return string|null
     */
    public function generatePublicUrl(ResourceInterface $resource, $absolute = false, $locale = null)
    {
        if (null === $event = $this->dispatcher->createResourceEvent($resource, false)) {
            return null;
        }

        $event->addData('_locale', $locale);

        $name = $this->dispatcher->getResourceEventName($resource, 'public_url');

        $this->dispatcher->dispatch($name, $event);

        if (!$event->hasData('route')) {
            return null;
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        $parameters = $event->hasData('parameters') ? $event->getData('parameters') : [];

        if ($locale) {
            $parameters['_locale'] = $locale;
        }

        try {
            return $this->router->generate($event->getData('route'), $parameters, $type);
        } catch (RoutingException $e) {
            return null;
        }
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
            return Actions::LIST; // TODO or VIEW ?
        } elseif ($action == 'SHOW') {
            return Actions::VIEW;
        } elseif ($action == 'NEW') {
            return Actions::CREATE;
        } elseif ($action == 'EDIT') {
            return Actions::EDIT;
        } elseif ($action == 'REMOVE') {
            return Actions::DELETE;
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
