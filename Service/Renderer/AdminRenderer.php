<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Renderer;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Service\Pin\PinHelper;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingUtil;
use Ekyna\Bundle\UiBundle\Service\UiRenderer;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Throwable;

use function array_key_exists;
use function array_merge;
use function implode;
use function in_array;
use function json_encode;
use function sprintf;

/**
 * Class AdminRenderer
 * @package Ekyna\Bundle\AdminBundle\Service\Renderer
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class AdminRenderer
{
    private ResourceHelper                $resourceHelper;
    private PinHelper                     $pinHelper;
    private AuthorizationCheckerInterface $authorization;
    private UiRenderer                    $uiRenderer;
    private array                         $config;

    public function __construct(
        ResourceHelper $resourceHelper,
        PinHelper $pinHelper,
        AuthorizationCheckerInterface $authorizationChecker,
        UiRenderer $uiRenderer,
        array $config
    ) {
        $this->resourceHelper = $resourceHelper;
        $this->pinHelper = $pinHelper;
        $this->authorization = $authorizationChecker;
        $this->uiRenderer = $uiRenderer;
        $this->config = $config;
    }

    /**
     * Returns the navbar config.
     */
    public function getNavbarConfig(): array
    {
        return $this->config['navbar'];
    }

    /**
     * Renders the stylesheets link tags.
     */
    public function renderStylesheets(): string
    {
        $output = '';

        foreach ($this->config['stylesheets'] as $path) {
            $output .= $this->uiRenderer->buildStylesheetTag($path);
        }

        return $output;
    }

    /**
     * Renders a resource action button.
     */
    public function renderResourceButton(
        $resource,
        string $action = ReadAction::class,
        array $options = [],
        array $attributes = []
    ): string {
        // TODO Register and use ResourceButtonGeneratorInterface
        if (in_array($action, ['public', 'editor'], true)) {
            if (null !== $url = $this->resourceHelper->generatePublicUrl($resource)) {
                if ($action === 'public') {
                    $label = 'resource.button.show_front';
                    $icon = 'eye-open';
                    $path = $url;
                } else {
                    $label = 'resource.button.show_editor';
                    $icon = 'edit';
                    $path = $this->resourceHelper->getUrlGenerator()->generate('admin_ekyna_cms_editor_index', [
                        'path' => $url,
                    ]);
                }

                return $this->uiRenderer->renderButton($label, [
                    'type'         => 'link',
                    'path'         => $path,
                    'icon'         => $icon,
                    'trans_domain' => 'EkynaAdmin',
                ], [
                    'target' => '_blank',
                ]);
            }

            return '';
        }

        $resourceConfig = $this->resourceHelper->getResourceConfig($resource);
        $actionConfig = $this->resourceHelper->getActionConfig($action);
        if (!$resourceConfig->hasAction($actionConfig->getClass())) {
            $message = sprintf(
                'Resource %s does not have %s action.',
                $resourceConfig->getId(),
                $actionConfig->getClass()
            );
            throw new LogicException($message);
        }

        if (!$this->resourceHelper->isGranted($action, $resource)) {
            return '';
        }

        // TODO Not needed if label, theme and icon are defined in options
        if (empty($button = $actionConfig->getButton())) {
            throw new LogicException(sprintf('Action %s has no button configured.', $action));
        }

        $options = array_merge($button, $options);

        $label = $options['label'] ?? null;

        /**
         * @TODO Check if prefixed translation exists.
         * @see \Symfony\Component\Translation\MessageCatalogueInterface::defines
         */
        /*if (is_string($label) && (0 === strpos($label, 'button.')) && !($options['short'] ?? false)) {
            $config = $this->resourceHelper->getResourceConfig($resource);
            $label = sprintf('%s.%s', $config->getTransPrefix(), $label);
            $options['trans_domain'] = $config->getTransDomain();
        }*/

        if (!array_key_exists('path', $options)) {
            $options['path'] = $this
                ->resourceHelper
                ->generateResourcePath($resource, $action, $options['parameters'] ?? []);
        }

        if (!array_key_exists('type', $options)) {
            $options['type'] = 'link';
        }

        unset($options['label'], $options['short'], $options['parameters']);

        return $this->uiRenderer->renderButton(
            $label,
            $options,
            $attributes
        );
    }

    /**
     * Returns whether the user has access granted or not on the given resource for the given action.
     *
     * @param string|object $resource
     */
    public function hasResourceAccess($resource, string $action = 'view'): bool
    {
        return $this->resourceHelper->isGranted($action, $resource);
    }

    /**
     * Returns the resource path.
     *
     * @TODO PHP8 Union types
     *
     * @param string|object $resource
     */
    public function generateResourcePath($resource, string $action = ReadAction::class, array $parameters = []): string
    {
        return $this->resourceHelper->generateResourcePath($resource, $action, $parameters);
    }

    /**
     * Returns the subject summary path.
     */
    public function generateSummaryPath(ResourceInterface $resource, bool $asAttribute = true): ?string
    {
        try {
            if ($asAttribute) {
                $resourceConfig = $this->resourceHelper->getResourceConfig($resource);
                $actionConfig = $this->resourceHelper->getActionConfig(SummaryAction::class);

                return sprintf(" data-summary='%s'", json_encode([
                    'route'      => RoutingUtil::getRouteName($resourceConfig, $actionConfig),
                    'parameters' => [
                        RoutingUtil::getRouteParameter($resourceConfig) => $resource->getId(),
                    ],
                ]));
            }

            return $this->resourceHelper->generateResourcePath($resource, SummaryAction::class);
        } catch (Throwable $exception) {
        }

        return null;
    }

    /**
     * Renders the resource pin link.
     */
    public function renderResourcePin(ResourceInterface $resource): string
    {
        $config = $this->resourceHelper->getResourceConfig($resource);

        $class = 'user-pin';
        if ($this->pinHelper->isPinnedResource($resource)) {
            $route = 'admin_pin_resource_unpin';
            $class .= ' unpin';
        } else {
            $route = 'admin_pin_resource_pin';
        }

        $parameters = [
            'name'       => $config->getId(),
            'identifier' => $resource->getId(),
        ];

        $path = $this->resourceHelper->getUrlGenerator()->generate($route, $parameters);

        return <<<EOT
<a href="$path" class="$class" data-resource="{$config->getId()}" data-identifier="{$resource->getId()}">
    <span class="glyphicon glyphicon-pushpin"></span>
</a>
EOT;
    }

    /**
     * Renders the front admin helper.
     */
    public function renderFrontHelper(ResourceInterface $resource): string
    {
        if (!$this->authorization->isGranted('ROLE_ADMIN')) {
            return '';
        }

        $buttons = [];

        if ($this->hasResourceAccess($resource)) {
            $url = $this->resourceHelper->generateResourcePath($resource, ReadAction::class);

            if (null !== $url) {
                $buttons[] = $this
                    ->uiRenderer
                    ->renderButton('resource.button.show_admin', [
                        'path'         => $url,
                        'type'         => 'link',
                        'trans_domain' => 'EkynaAdmin',
                    ]);
            }
        }

        if (empty($buttons)) {
            return '';
        }

        return '<div id="admin-helper">' . implode('', $buttons) . '</div>';
    }
}
