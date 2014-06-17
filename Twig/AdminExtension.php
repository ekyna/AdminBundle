<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry;
use Ekyna\Bundle\CoreBundle\Twig\UiExtension;
use Symfony\Component\Routing\RouterInterface;
use Symfony\Component\PropertyAccess\PropertyAccess;

/**
 * AdminExtension.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends \Twig_Extension
{
    /**
     * @var \Ekyna\Bundle\AdminBundle\Pool\ConfigurationRegistry
     */
    private $registry;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Security\ResourceAccessVoterInterface
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
    ) {
        $this->registry    = $registry;
        $this->aclOperator = $aclOperator;
        $this->router      = $router;
        $this->ui          = $ui;
        $this->logoPath    = $logoPath;
    }

    /**
     * {@inheritDoc}
     */
	public function getFunctions()
	{
	    return array(
	        new \Twig_SimpleFunction('admin_resource_btn', array($this, 'renderResourceButton'), array('is_safe' => array('html'))),
	        new \Twig_SimpleFunction('admin_resource_access', array($this, 'hasResourceAccess')),
	    );
	}

	/**
	 * Renders a resource action button.
	 * 
	 * @param object $resource
	 * @param array  $options
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
        	        $label = 'ekyna_core.button.'.$action;
    	        }
    	        unset($options['short']);
    	    }
    	    if(null === $label) {
        	    $config = $this->registry->findConfiguration($resource);
    	        $label = sprintf('%s.button.%s', $config->getId(), $action);
    	    }

    	    if(! array_key_exists('path', $options)) {
    	        $options['path'] = $this->generatePath($resource, $action);
    	    }
    	    if(! array_key_exists('type', $options)) {
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
	 * @param mixed $resource
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
	 * @return string
	 */
	private function generatePath($resource, $action)
	{
	    $config = $this->registry->findConfiguration($resource);
	    $routeName = $config->getRoute($action);

	    $accessor = PropertyAccess::createPropertyAccessor();
	    $parameters = array();
	    if (null === $route = $this->router->getRouteCollection()->get($routeName)) {
	         return '';
	    }
	    $requirements = $route->getRequirements();
	    $current = $resource;

	    while (array_key_exists($config->getResourceName().'Id', $requirements)) {
	        $parameters[$config->getResourceName().'Id'] = $accessor->getValue($current, 'id');
	        if (null !== $config->getParentId()) {
	            $config = $this->registry->findConfiguration($config->getParentId());
	            $current = $accessor->getValue($current, $config->getResourceName());
	        } else {
	            break;
	        }
	    }
        
	    // TODO:  try / catch
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
    	        'icon'  => 'plus',
	        );
	    } elseif ($action == 'edit') {
	        return array(
	        	'theme' => 'warning',
    	        'icon'  => 'pencil',
	        );
	    } elseif ($action == 'remove') {
	        return array(
	        	'theme' => 'danger',
    	        'icon'  => 'trash',
	        );
	    } elseif ($action == 'show') {
	        return array(
    	        'icon'  => 'eye-open',
	        );
	    } elseif ($action == 'list') {
	        return array(
    	        'icon'  => 'list',
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
