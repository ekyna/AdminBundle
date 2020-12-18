<?php

namespace Ekyna\Bundle\AdminBundle\Helper;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\ClassMetadataInfo;
use Ekyna\Component\Resource\Configuration\ConfigurationRegistry;
use Ekyna\Component\Resource\Dispatcher\ResourceEventDispatcherInterface;
use Ekyna\Component\Resource\Model\Actions;
use Ekyna\Component\Resource\Model\ResourceInterface;
use RuntimeException;
use Symfony\Component\PropertyAccess\PropertyAccess;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Routing\Exception\ExceptionInterface as RoutingException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Routing\Route;
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
     * @var PropertyAccessorInterface
     */
    private $accessor;


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
     * Returns the entity manager.
     *
     * @return EntityManagerInterface
     */
    public function getEntityManager(): EntityManagerInterface
    {
        return $this->manager;
    }

    /**
     * Returns the registry.
     *
     * @return ConfigurationRegistry
     */
    public function getRegistry(): ConfigurationRegistry
    {
        return $this->registry;
    }

    /**
     * Returns the url generator.
     *
     * @return UrlGeneratorInterface
     */
    public function getUrlGenerator(): UrlGeneratorInterface
    {
        return $this->router;
    }

    /**
     * Returns whether the user has access granted or not on the given resource for the given action.
     *
     * @param string|object $resource
     * @param string        $action
     *
     * @return bool
     */
    public function isGranted($resource, string $action = 'view'): bool
    {
        return $this->authorization->isGranted($this->getPermission($action), $resource);
    }

    /**
     * Generates an admin path for the given resource and action.
     *
     * @param string|object $resource
     * @param string        $action
     * @param array         $parameters
     * @param bool          $absolute
     *
     * @return string
     *
     * @throws RuntimeException
     */
    public function generateResourcePath(
        $resource,
        string $action = 'show',
        array $parameters = [],
        bool $absolute = false
    ): string {
        $configuration = $this->registry->findConfiguration($resource);
        $routeName = $configuration->getRoute($action);
        $route = $this->findRoute($routeName);

        if ($resource instanceof ResourceInterface) {
            $parameters = $this->buildParameters($route, $resource, $parameters);
        }

        $type = $absolute ? UrlGeneratorInterface::ABSOLUTE_URL : UrlGeneratorInterface::ABSOLUTE_PATH;

        return $this->router->generate($routeName, $parameters, $type);
    }

    /**
     * Builds the route parameters.
     *
     * @param Route                  $route
     * @param ResourceInterface|null $resource
     * @param array                  $parameters
     *
     * @return array
     */
    public function buildParameters(Route $route, ResourceInterface $resource, array $parameters = []): array
    {
        $accessor = $this->getAccessor();

        $entities = [];
        if (is_object($resource)) {
            $configuration = $this->registry->findConfiguration($resource);

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

        $requirements = $route->getRequirements();
        foreach ($entities as $name => $resource) {
            $parameter = $name . 'Id';
            if (array_key_exists($parameter, $requirements) && !isset($parameters[$parameter])) {
                $parameters[$parameter] = $accessor->getValue($resource, 'id');
            }
        }

        return $parameters;
    }

    /**
     * Returns the public url for the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $absolute
     * @param string|null       $locale
     *
     * @return string|null
     */
    public function generatePublicUrl(
        ResourceInterface $resource,
        bool $absolute = false,
        string $locale = null
    ): ?string {
        return $this->generateUrl($resource, 'public_url', $absolute, $locale);
    }

    /**
     * Returns the image url for the given resource.
     *
     * @param ResourceInterface $resource
     * @param bool              $absolute
     *
     * @return string|null
     */
    public function generateImageUrl(ResourceInterface $resource, bool $absolute = false): ?string
    {
        return $this->generateUrl($resource, 'image_url', $absolute);
    }

    /**
     * Returns the permission for the given action.
     *
     * @param string $action
     *
     * @return string
     */
    public function getPermission(string $action): string
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
     * @return Route|null
     */
    public function findRoute(string $routeName, bool $throw = true): ?Route
    {
        //  TODO create a route finder ? (same in CmsBundle BreadcrumbBuilder)
        $i18nRouterClass = 'JMS\I18nRoutingBundle\Router\I18nRouterInterface';
        if (interface_exists($i18nRouterClass) && $this->router instanceof $i18nRouterClass) {
            $route = $this->router->getOriginalRouteCollection()->get($routeName);
        } else {
            $route = $this->router->getRouteCollection()->get($routeName);
        }
        if (null === $route && $throw) {
            throw new RuntimeException(sprintf('Route "%s" not found.', $routeName));
        }

        return $route;
    }

    /**
     * Returns the public url for the given resource.
     *
     * @param ResourceInterface $resource
     * @param string            $name
     * @param bool              $absolute
     * @param string|null       $locale
     *
     * @return string|null
     */
    protected function generateUrl(
        ResourceInterface $resource,
        string $name,
        bool $absolute = false,
        string $locale = null
    ): ?string {
        if (null === $event = $this->dispatcher->createResourceEvent($resource, false)) {
            return null;
        }

        $event->addData('_locale', $locale);
        $eventName = $this->dispatcher->getResourceEventName($resource, $name);

        /** @noinspection PhpParamsInspection */
        $this->dispatcher->dispatch($eventName, $event);

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
     * Returns the property accessor.
     *
     * @return PropertyAccessorInterface
     */
    protected function getAccessor(): PropertyAccessorInterface
    {
        if ($this->accessor) {
            return $this->accessor;
        }

        return $this->accessor = PropertyAccess::createPropertyAccessor();
    }
}
