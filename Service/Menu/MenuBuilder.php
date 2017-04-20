<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Menu;

use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingUtil;
use Ekyna\Component\Resource\Config\ActionConfig;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use UnexpectedValueException;

use function array_replace;
use function is_null;
use function sprintf;

/**
 * Class MenuBuilder
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MenuBuilder
{
    private FactoryInterface $factory;
    private MenuPool         $pool;
    private ResourceHelper   $helper;

    private ?ItemInterface   $sideMenu           = null;
    private ?ItemInterface   $breadcrumb         = null;
    private ?OptionsResolver $breadcrumbResolver = null;

    public function __construct(
        FactoryInterface $factory,
        MenuPool         $pool,
        ResourceHelper   $helper
    ) {
        $this->factory = $factory;
        $this->pool = $pool;
        $this->helper = $helper;
    }

    /**
     * Builds backend sidebar menu.
     */
    public function createSideMenu(): ItemInterface
    {
        if (null !== $this->sideMenu) {
            return $this->sideMenu;
        }

        $this->pool->prepare();

        $this->sideMenu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'id' => 'sidebar-menu',
            ],
        ]);

        $this->sideMenu
            ->addChild('dashboard', [
                'route'           => 'admin_dashboard',
                'labelAttributes' => ['icon' => 'dashboard'],
            ])
            ->setLabel('dashboard')
            ->setExtra('translation_domain', 'EkynaAdmin');

        $this->appendChildren($this->sideMenu);

        return $this->sideMenu;
    }

    /**
     * Create if not exists and returns the breadcrumb.
     */
    public function createBreadcrumb(): ItemInterface
    {
        if (null !== $this->breadcrumb) {
            return $this->breadcrumb;
        }

        $this->breadcrumb = $this
            ->factory
            ->createItem('root', ['childrenAttributes' => ['class' => 'breadcrumb hidden-xs']]);

        $this
            ->breadcrumb
            ->addChild('dashboard', ['route' => 'admin_dashboard'])
            ->setLabel('dashboard')
            ->setExtra('translation_domain', 'EkynaAdmin');

        return $this->breadcrumb;
    }

    /**
     * Appends a breadcrumb element.
     */
    public function breadcrumbAppend(array $data): MenuBuilder
    {
        $data = $this->resolveBreadcrumbItem($data);

        $item = $this
            ->createBreadcrumb()
            ->addChild($data['name'], [
                'uri'             => $data['uri'] ?? null,
                'route'           => $data['route'],
                'routeParameters' => $data['parameters'],
            ])
            ->setLabel($data['label']);

        if (!empty($data['trans_domain'])) {
            $item->setExtra('translation_domain', $data['trans_domain']);
        }

        return $this;
    }

    /**
     * Fills the menu with menu pool's groups and entries.
     */
    private function appendChildren(ItemInterface $menu): void
    {
        foreach ($this->pool->getGroups() as $group) {
            $groupOptions = [
                'label'              => $group->getLabel(),
                'labelAttributes'    => ['icon' => $group->getIcon()],
                'childrenAttributes' => ['class' => 'submenu'],
                'extras'             => [
                    'translation_domain' => $group->getDomain(),
                ],
            ];

            if ($group->hasEntries()) {
                $groupOptions['labelAttributes']['class'] = 'dropdown-toggle';

                $groupEntries = [];
                foreach ($group->getEntries() as $entry) {
                    if (null === $groupEntry = $this->createEntryItem($group, $entry)) {
                        continue;
                    }

                    $groupEntries[] = $groupEntry;
                }

                if (empty($groupEntries)) {
                    continue;
                }

                $menuGroup = $menu->addChild($group->getName(), $groupOptions);

                foreach ($groupEntries as $groupEntry) {
                    $menuGroup->addChild($groupEntry);
                }

                continue;
            }

            $groupOptions['route'] = $group->getRoute();
            $menu->addChild($group->getName(), $groupOptions);
        }
    }

    /**
     * Creates the entry menu item.
     */
    private function createEntryItem(MenuGroup $group, MenuEntry $entry): ?ItemInterface
    {
        $rCfg = null;
        if ($resource = $entry->getResource()) {
            $rCfg = $this->helper->getResourceConfig($resource);
        }

        if (!$route = $entry->getRoute()) {
            if (!$rCfg || !$entry->getAction()) {
                if ($rCfg->hasAction(ListAction::class)) {
                    $entry->setAction(ListAction::class);
                } else {
                    $message = sprintf(
                        "Either 'route' or 'resource' and 'action' options must be set for '%s.%s' menu entry.",
                        $group->getName(),
                        $entry->getName()
                    );
                    throw new UnexpectedValueException($message);
                }
            }

            if (!$this->helper->isGranted($entry->getAction(), $resource)) {
                return null;
            }

            $route = $this->helper->getRoute($rCfg->getId(), $entry->getAction());
        } elseif ($rCfg && ($permission = $entry->getPermission())) {
            if (!$this->helper->isGranted($permission, $resource)) {
                return null;
            }
        }

        if ($label = $entry->getLabel()) {
            $domain = $entry->getDomain();
        } else {
            if (!$rCfg) {
                throw new UnexpectedValueException(sprintf(
                    "Either 'label' or 'resource' option must be set for '%s.%s' menu entry.",
                    $group->getName(),
                    $entry->getName()
                ));
            }

            $label = $rCfg->getResourceLabel(true);
            $domain = $rCfg->getTransDomain();
        }

        return $this->factory->createItem($entry->getName(), [
            'label'  => $label,
            'route'  => $route,
            'extras' => [
                'translation_domain' => $domain,
            ],
        ]);
    }

    /**
     * Resolves the breadcrumb item options.
     */
    private function resolveBreadcrumbItem(array $data): array
    {
        $data = $this->getBreadcrumbResolver()->resolve($data);

        $actionCfg = $data['action_config'] ??
            (isset($data['action']) ? $this->helper->getActionConfig($data['action']) : null);

        $resourceCfg = $data['resource_config'] ??
            (isset($data['resource']) ? $this->helper->getResourceConfig($data['resource']) : null);

        // Name
        if (is_null($data['name'])) {
            if (is_null($actionCfg) || is_null($resourceCfg)) {
                throw new InvalidOptionsException(
                    "You must either configure 'name' option or 'action' and 'resource' options."
                );
            }

            $data['name'] = $resourceCfg->getId() . '_' . $actionCfg->getName();
        }

        // Label & translation domain
        if (is_null($data['label'])) {
            if (!$actionCfg) {
                throw new InvalidOptionsException(
                    "You must either configure 'label' or 'action' option."
                );
            }

            if ($actionCfg->getClass() === ListAction::class) {
                if (!$resourceCfg) {
                    throw new InvalidOptionsException('Failed to generate label.');
                }
                $data['label'] = $resourceCfg->getResourceLabel(true);
                $data['trans_domain'] = $resourceCfg->getTransDomain();
            } elseif ($actionCfg->getClass() === ReadAction::class) {
                if (!$data['resource'] instanceof ResourceInterface) {
                    throw new InvalidOptionsException('Failed to generate label.');
                }

                $data['label'] = (string)$data['resource'];
                $data['trans_domain'] = false;
            } elseif ($button = $actionCfg->getButton()) {
                $data['label'] = $button['label'];
                $data['trans_domain'] = $button['trans_domain'];
            } else {
                throw new InvalidOptionsException('Failed to generate label.');
            }
        }

        // Route
        if (is_null($data['route'])) {
            if (is_null($actionCfg) || is_null($resourceCfg)) {
                throw new InvalidOptionsException(
                    "You must either configure 'route' option or 'action' and 'resource' options."
                );
            }

            if ($data['resource'] instanceof ResourceInterface) {
                $data['uri'] = $this->helper->generateResourcePath($data['resource'], $actionCfg->getName(), $data['parameters']);
            } else {
                $data['uri'] = $this->helper->generateResourcePath($resourceCfg->getId(), $actionCfg->getName(), $data['parameters']);
            }
        } elseif ($data['resource'] instanceof ResourceInterface) {
            $data['parameters'] = array_replace($data['parameters'], [
                RoutingUtil::getRouteParameter($resourceCfg) => $data['resource']->getId(),
            ]);
        }

        unset(
            $data['action'],
            $data['action_config'],
            $data['resource'],
            $data['resource_config'],
        );

        return $data;
    }

    /**
     * Returns the breadcrumb item options resolved.
     */
    private function getBreadcrumbResolver(): OptionsResolver
    {
        if ($this->breadcrumbResolver) {
            return $this->breadcrumbResolver;
        }

        $resolver = new OptionsResolver();
        $resolver
            ->setDefaults([
                'name'            => null,
                'label'           => null,
                'trans_domain'    => null,
                'route'           => null,
                'parameters'      => [],
                'action'          => null,
                'action_config'   => null,
                'resource'        => null,
                'resource_config' => null,
            ])
            ->setAllowedTypes('name', ['string', 'null'])
            ->setAllowedTypes('label', ['string', 'null'])
            ->setAllowedTypes('trans_domain', ['string', 'null'])
            ->setAllowedTypes('route', ['string', 'bool', 'null'])
            ->setAllowedTypes('parameters', 'array')
            ->setAllowedTypes('action', ['string', 'null'])
            ->setAllowedTypes('action_config', [ActionConfig::class, 'null'])
            ->setAllowedTypes('resource', [ResourceInterface::class, 'string', 'null'])
            ->setAllowedTypes('resource_config', [ResourceConfig::class, 'null'])
            ->setAllowedValues('route', function ($value) {
                if (true === $value) {
                    return false;
                }

                return true;
            });

        return $this->breadcrumbResolver = $resolver;
    }
}
