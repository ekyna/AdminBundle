<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\CoreBundle\Twig\UiExtension;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends \Twig_Extension
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
     * @var string
     */
    private $logoPath;

    /**
     * @var UiExtension
     */
    private $ui;

    /**
     * Constructor.
     *
     * @param \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry $registry
     * @param \Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface   $aclOperator
     * @param \Symfony\Component\Routing\RouterInterface           $router
     * @param \Ekyna\Bundle\CoreBundle\Twig\UiExtension            $ui
     * @param string                                               $logoPath
     */
    public function __construct(
        ConfigurationRegistry $registry,
        AclOperatorInterface $aclOperator,
        RouterInterface $router,
        UiExtension $ui,
        $logoPath
    )
    {
        $this->registry = $registry;
        $this->aclOperator = $aclOperator;
        $this->router = $router;
        $this->ui = $ui;
        $this->logoPath = $logoPath;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return array(
            new \Twig_SimpleFunction('admin_resource_btn', array($this, 'renderResourceButton'), array('is_safe' => array('html'))),
            new \Twig_SimpleFunction('admin_resource_access', array($this, 'hasResourceAccess')),
            new \Twig_SimpleFunction('admin_resource_path', array($this, 'generateResourcePath')),
        );
    }

    /**
     * Renders a resource action button.
     *
     * @param object $resource
     * @param string $action
     * @param array  $options
     * @param array  $attributes
     *
     * @return string
     */
    public function renderResourceButton($resource, $action, array $options = array(), array $attributes = array())
    {
        if ($this->hasResourceAccess($resource, $action)) {
            $options = array_merge($this->getButtonOptions($action), $options);

            $label = null;
            if (array_key_exists('label', $options)) {
                $label = $options['label'];
                unset($options['label']);
            } elseif (array_key_exists('short', $options)) {
                if ($options['short']) {
                    $label = 'ekyna_core.button.' . $action;
                }
                unset($options['short']);
            }
            if (null === $label) {
                $config = $this->registry->findConfiguration($resource);
                $label = sprintf('%s.button.%s', $config->getId(), $action);
            }

            if (!array_key_exists('path', $options)) {
                $options['path'] = $this->generateResourcePath($resource, $action);
            }
            if (!array_key_exists('type', $options)) {
                $options['type'] = 'link';
            }

            return $this->ui->renderButton(
                $label,
                $options,
                $attributes
            );
        }

        return '';
    }

    /**
     * Returns whether the user has access granted or not on the given resource for the given action.
     *
     * @param mixed  $resource
     * @param string $action
     *
     * @return boolean
     */
    public function hasResourceAccess($resource, $action = 'view')
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

        $entities = array(
            $configuration->getResourceName() => $resource
        );
        $current = $resource;
        while (null !== $configuration->getParentId()) {
            $configuration = $this->registry->findConfiguration($configuration->getParentId());
            $current = $accessor->getValue($current, $configuration->getResourceName());
            $entities[$configuration->getResourceName()] = $current;
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
    private function getPermission($action)
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
     * Returns the default button options for the given action.
     *
     * @param string $action
     *
     * @return array
     */
    private function getButtonOptions($action)
    {
        if ($action == 'new') {
            return array(
                'theme' => 'primary',
                'icon' => 'plus',
            );
        } elseif ($action == 'edit') {
            return array(
                'theme' => 'warning',
                'icon' => 'pencil',
            );
        } elseif ($action == 'remove') {
            return array(
                'theme' => 'danger',
                'icon' => 'trash',
            );
        } elseif ($action == 'show') {
            return array(
                'icon' => 'eye-open',
            );
        } elseif ($action == 'list') {
            return array(
                'icon' => 'list',
            );
        }
        return array();
    }

    /**
     * {@inheritDoc}
     */
    public function getGlobals()
    {
        return array(
            'ekyna_admin_logo_path' => $this->logoPath,
        );
    }

    /**
     * {@inheritDoc}
     */
    public function getName()
    {
        return 'ekyna_admin';
    }
}
