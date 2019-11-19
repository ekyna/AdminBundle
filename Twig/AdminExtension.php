<?php

namespace Ekyna\Bundle\AdminBundle\Twig;

use Ekyna\Bundle\AdminBundle\Entity\UserPin;
use Ekyna\Bundle\AdminBundle\Helper\ResourceHelper;
use Ekyna\Bundle\AdminBundle\Helper\PinHelper;
use Ekyna\Bundle\CoreBundle\Service\Ui\UiRenderer;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Twig
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends \Twig_Extension
{
    /**
     * @var ResourceHelper
     */
    private $resourceHelper;

    /**
     * @var PinHelper
     */
    private $pinHelper;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorizationChecker;

    /**
     * @var UiRenderer
     */
    private $uiRenderer;

    /**
     * @var array
     */
    private $config;


    /**
     * Constructor.
     *
     * @param ResourceHelper                $resourceHelper
     * @param PinHelper                     $pinHelper
     * @param AuthorizationCheckerInterface $authorizationChecker
     * @param UiRenderer                    $uiRenderer
     * @param array                         $config
     */
    public function __construct(
        ResourceHelper $resourceHelper,
        PinHelper $pinHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        UiRenderer $uiRenderer,
        array $config
    ) {
        $this->resourceHelper = $resourceHelper;
        $this->pinHelper = $pinHelper;
        $this->authorizationChecker = $authorizationChecker;
        $this->uiRenderer = $uiRenderer;
        $this->config = $config;
    }

    /**
     * {@inheritDoc}
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction(
                'admin_logo_path',
                [$this, 'getLogoPath']
            ),
            new \Twig_SimpleFunction(
                'admin_navbar_config',
                [$this, 'getNavbarConfig']
            ),
            new \Twig_SimpleFunction(
                'admin_stylesheets',
                [$this, 'renderStylesheets'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'admin_resource_btn',
                [$this, 'renderResourceButton'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'admin_resource_access',
                [$this, 'hasResourceAccess']
            ),
            new \Twig_SimpleFunction(
                'admin_resource_path',
                [$this, 'generateResourcePath']
            ),
            new \Twig_SimpleFunction(
                'admin_user_pins',
                [$this, 'getUserPins']
            ),
            new \Twig_SimpleFunction(
                'admin_resource_pin',
                [$this, 'renderResourcePin'],
                ['is_safe' => ['html']]
            ),
            new \Twig_SimpleFunction(
                'admin_front_helper',
                [$this, 'renderFrontHelper'],
                ['is_safe' => ['html']]
            ),
        ];
    }

    /**
     * Returns the logo path.
     *
     * @return string
     */
    public function getLogoPath()
    {
        return $this->config['logo_path'];
    }

    /**
     * Returns the navbar config.
     *
     * @return string
     */
    public function getNavbarConfig()
    {
        return $this->config['navbar'];
    }

    /**
     * Renders the stylesheets link tags.
     *
     * @return string
     */
    public function renderStylesheets()
    {
        $output = '';

        foreach ($this->config['stylesheets'] as $path) {
            $output .= $this->uiRenderer->buildStylesheetTag($path);
        }

        return $output;
    }

    /**
     * Renders a resource action button.
     *
     * @param mixed  $resource
     * @param string $action
     * @param array  $options
     * @param array  $attributes
     *
     * @return string
     */
    public function renderResourceButton($resource, $action = 'show', array $options = [], array $attributes = [])
    {
        if (in_array($action, ['public', 'editor'], true)) {
            if (null !== $url = $this->resourceHelper->generatePublicUrl($resource)) {
                if ($action === 'public') {
                    $label = 'ekyna_admin.resource.button.show_front';
                    $icon = 'eye-open';
                    $path = $url;
                } else {
                    $label = 'ekyna_admin.resource.button.show_editor';
                    $icon = 'edit';
                    $path = $this->resourceHelper->getUrlGenerator()->generate('ekyna_cms_editor_index', [
                        'path' => $url,
                    ]);
                }

                return $this->uiRenderer->renderButton($label, [
                    'type' => 'link',
                    'path' => $path,
                    'icon' => $icon,
                ], [
                    'target' => '_blank',
                ]);
            }

            return '';
        }

        if ($this->resourceHelper->isGranted($resource, $action)) {
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
                $config = $this->resourceHelper->getRegistry()->findConfiguration($resource);
                $label = sprintf('%s.button.%s', $config->getTranslationPrefix(), $action);
            }

            if (!array_key_exists('path', $options)) {
                $options['path'] = $this->resourceHelper->generateResourcePath($resource, $action);
            }
            if (!array_key_exists('type', $options)) {
                $options['type'] = 'link';
            }

            return $this->uiRenderer->renderButton(
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
     * @return bool
     */
    public function hasResourceAccess($resource, $action = 'view')
    {
        return $this->resourceHelper->isGranted($resource, $action);
    }

    /**
     * Returns the resource path.
     *
     * @param mixed  $resource
     * @param string $action
     * @param array  $parameters
     *
     * @return string
     */
    public function generateResourcePath($resource, $action = 'show', array $parameters = [])
    {
        return $this->resourceHelper->generateResourcePath($resource, $action, $parameters);
    }

    /**
     * Returns the user pins.
     *
     * @return UserPin[]
     */
    public function getUserPins()
    {
        return $this->pinHelper->getUserPins();
    }

    /**
     * Renders the resource pin link.
     *
     * @param ResourceInterface $resource
     *
     * @return string
     */
    public function renderResourcePin(ResourceInterface $resource)
    {
        $config = $this->resourceHelper->getRegistry()->findConfiguration($resource);

        $class = 'user-pin';
        if ($this->pinHelper->isPinnedResource($resource)) {
            $route = 'ekyna_admin_pin_resource_unpin';
            $class .= ' unpin';
        } else {
            $route = 'ekyna_admin_pin_resource_pin';
        }

        $parameters = [
            'name'       => $config->getResourceId(),
            'identifier' => $resource->getId(),
        ];

        $path = $this->resourceHelper->getUrlGenerator()->generate($route, $parameters);

        return <<<EOT
<a href="$path" class="$class" data-resource="{$config->getResourceId()}" data-identifier="{$resource->getId()}">
    <span class="glyphicon glyphicon-pushpin"></span>
</a>
EOT;
    }

    /**
     * Renders the front admin helper.
     *
     * @param ResourceInterface $resource
     *
     * @return string
     */
    public function renderFrontHelper(ResourceInterface $resource)
    {
        if (!$this->authorizationChecker->isGranted('ROLE_ADMIN')) {
            return '';
        }

        $buttons = [];

        if ($this->hasResourceAccess($resource)) {
            if (null !== $url = $this->resourceHelper->generateResourcePath($resource)) {
                $buttons[] = $this->uiRenderer->renderButton(
                    'ekyna_admin.resource.button.show_admin',
                    ['path' => $url, 'type' => 'link']
                );
            }
        }

        if (empty($buttons)) {
            return '';
        }

        return '<div id="admin-helper">' . implode('', $buttons) . '</div>';
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
            return [
                'theme' => 'primary',
                'icon'  => 'plus',
            ];
        } elseif ($action == 'edit') {
            return [
                'theme' => 'warning',
                'icon'  => 'pencil',
            ];
        } elseif ($action == 'remove') {
            return [
                'theme' => 'danger',
                'icon'  => 'trash',
            ];
        } elseif ($action == 'show') {
            return [
                'icon' => 'eye-open',
            ];
        } elseif ($action == 'list') {
            return [
                'icon' => 'list',
            ];
        }

        return [];
    }
}
