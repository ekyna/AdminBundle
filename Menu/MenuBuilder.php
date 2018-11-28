<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

use Ekyna\Component\Resource\Model\Actions;
use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * Class MenuBuilder
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MenuBuilder
{
    /**
     * @var FactoryInterface
     */
    private $factory;

    /**
     * @var TranslatorInterface
     */
    private $translator;

    /**
     * @var MenuPool
     */
    private $pool;

    /**
     * @var AuthorizationCheckerInterface
     */
    private $authorization;

    /**
     * @var ItemInterface
     */
    private $breadcrumb;


    /**
     * Constructor.
     *
     * @param FactoryInterface              $factory
     * @param TranslatorInterface           $translator
     * @param MenuPool                      $pool
     * @param AuthorizationCheckerInterface $authorization
     */
    public function __construct(
        FactoryInterface $factory,
        TranslatorInterface $translator,
        MenuPool $pool,
        AuthorizationCheckerInterface $authorization
    ) {
        $this->factory = $factory;
        $this->translator = $translator;
        $this->pool = $pool;
        $this->authorization = $authorization;
    }

    /**
     * Builds backend sidebar menu.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createSideMenu()
    {
        $this->pool->prepare();

        $menu = $this->factory->createItem('root', [
            'childrenAttributes' => [
                'id' => 'sidebar-menu',
            ],
        ]);

        $menu
            ->addChild('dashboard', [
                'route'           => 'ekyna_admin_dashboard',
                'labelAttributes' => ['icon' => 'dashboard'],
            ])
            ->setLabel('ekyna_admin.dashboard');

        $this->appendChildren($menu);

        return $menu;
    }

    /**
     * Fills the menu with menu pool's groups and entries.
     *
     * @param \Knp\Menu\ItemInterface $menu
     */
    private function appendChildren(ItemInterface $menu)
    {
        foreach ($this->pool->getGroups() as $group) {

            $groupOptions = [
                'labelAttributes'    => ['icon' => $group->getIcon()],
                'childrenAttributes' => ['class' => 'submenu'],
            ];

            if ($group->hasEntries()) {
                $groupOptions['labelAttributes']['class'] = 'dropdown-toggle';

                $groupEntries = [];
                foreach ($group->getEntries() as $entry) {
                    if (!$this->entrySecurityCheck($entry)) {
                        continue;
                    }

                    $groupEntry = $this->factory->createItem($entry->getName(), [
                        'route' => $entry->getRoute(),
                    ]);
                    $groupEntry
                        ->setLabel($entry->getLabel())
                        ->setExtra('translation_domain', $entry->getDomain());

                    $groupEntries[] = $groupEntry;
                }

                if (0 < count($groupEntries)) {
                    $menuGroup = $menu
                        ->addChild($group->getName(), $groupOptions)
                        ->setLabel($group->getLabel())
                        ->setExtra('translation_domain', $group->getDomain());
                    foreach ($groupEntries as $groupEntry) {
                        $menuGroup->addChild($groupEntry);
                    }
                }
            } else {
                $groupOptions['route'] = $group->getRoute();
                $menu
                    ->addChild($group->getName(), $groupOptions)
                    ->setLabel($group->getLabel())
                    ->setExtra('translation_domain', $group->getDomain());
            }
        }
    }

    /**
     * Returns whether the user has access granted for the given entry.
     *
     * @param MenuEntry $entry
     *
     * @return boolean
     */
    private function entrySecurityCheck(MenuEntry $entry)
    {
        if (null !== $resource = $entry->getResource()) {
            return $this->authorization->isGranted(Actions::VIEW, $resource);
        }

        return true;
    }

    /**
     * Appends a breadcrumb element.
     *
     * @param string $name
     * @param string $label
     * @param string $route
     * @param array  $parameters
     */
    public function breadcrumbAppend($name, $label, $route = null, array $parameters = [])
    {
        $this->createBreadcrumb();

        $this
            ->breadcrumb
            ->addChild($name, ['route' => $route, 'routeParameters' => $parameters])
            ->setLabel($label);
    }

    /**
     * Create if not exists and returns the breadcrumb.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createBreadcrumb()
    {
        if (null === $this->breadcrumb) {
            $this->breadcrumb = $this
                ->factory
                ->createItem('root', ['childrenAttributes' => ['class' => 'breadcrumb hidden-xs']]);

            $this
                ->breadcrumb
                ->addChild('dashboard', ['route' => 'ekyna_admin_dashboard'])
                ->setLabel('ekyna_admin.dashboard');
        }

        return $this->breadcrumb;
    }
}
