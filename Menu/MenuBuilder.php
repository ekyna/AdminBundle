<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

use Knp\Menu\FactoryInterface;
use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Translation\TranslatorInterface;
use Ekyna\Bundle\AdminBundle\Security\ResourceAccessVoterInterface;

/**
 * Menu builder.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class MenuBuilder
{
    /**
     * @var \Knp\Menu\FactoryInterface
     */
    private $factory;

    /**
     * @var \Symfony\Component\Translation\TranslatorInterface
     */
    private $translator;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Menu\MenuPool
     */
    private $pool;

    /**
     * @var \Ekyna\Bundle\AdminBundle\Security\ResourceAccessVoterInterface
     */
    private $voter;

    /**
     * @var \Knp\Menu\ItemInterface
     */
    private $breadcrumb;


    /**
     * Constructor.
     *
     * @param \Knp\Menu\FactoryInterface                                      $factory
     * @param \Symfony\Component\Translation\TranslatorInterface              $translator
     * @param \Ekyna\Bundle\AdminBundle\Menu\MenuPool                         $pool
     * @param \Ekyna\Bundle\AdminBundle\Security\ResourceAccessVoterInterface $voter
     */
    public function __construct(
        FactoryInterface             $factory,
        TranslatorInterface          $translator, 
        MenuPool                     $pool,
        ResourceAccessVoterInterface $voter
    ) {
        $this->factory    = $factory;
        $this->translator = $translator;
        $this->pool       = $pool;
        $this->voter      = $voter;
    }

    /**
     * Builds backend sidebar menu.
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createSideMenu(Request $request)
    {
        $this->pool->prepare();

        $menu = $this->factory->createItem('root', array(
            'childrenAttributes' => array(
                'id' => 'dashboard-menu'
            )
        ));

        //$menu->setCurrent($request->getRequestUri());

        $childOptions = array(
            'childrenAttributes' => array(),
            'labelAttributes'    => array()
        );

        $menu
            ->addChild('dashboard', array(
                'route' => 'ekyna_admin_dashboard',
                'labelAttributes' => array('icon' => 'dashboard'),
            ))
            ->setLabel('ekyna_admin.dashboard')
        ;

        $this->appendChilds($menu, $childOptions);

        return $menu;
    }

    /**
     * Fills the menu with menu pool's groups and entries.
     *
     * @param \Knp\Menu\ItemInterface $menu
     * @param array                   $childOptions
     */
    private function appendChilds(ItemInterface $menu, array $childOptions)
    {
        foreach ($this->pool->getGroups() as $group) {

            $groupOptions = array(
                'labelAttributes' => array('icon' => $group->getIcon()),
                'childrenAttributes' => array('class' => 'submenu')
            );

            if ($group->hasEntries()) {
                $groupOptions['labelAttributes']['class'] = 'dropdown-toggle';

                $groupEntries = array();
                foreach ($group->getEntries() as $entry) {
                    if (!$this->entrySecurityCheck($entry)) {
                        continue;
                    }
                    
                    $groupEntry = $this->factory->createItem($entry->getName(), array(
                        'route' => $entry->getRoute()
                    ));
                    $groupEntry->setLabel($this->translate($entry->getLabel(), array(), $entry->getDomain()));
                    $groupEntries[] = $groupEntry;
                }

                if (0 < count($groupEntries)) {
                    $menuGroup = $menu
                        ->addChild($group->getName(), $groupOptions)
                        ->setLabel($this->translate($group->getLabel(), array(), $group->getDomain()))
                    ;
                    foreach ($groupEntries as $groupEntry) {
                        $menuGroup->addChild($groupEntry);
                    }
                }
            } else {
                $groupOptions['route'] = $group->getRoute();
                $menu
                    ->addChild($group->getName(), $groupOptions)
                    ->setLabel($this->translate($group->getLabel(), array(), $group->getDomain()))
                ;
            }
        }
    }

    /**
     * Returns whether the user has access granted for the given entry.
     * 
     * @return boolean
     */
    private function entrySecurityCheck(MenuEntry $entry)
    {
        if (null !== $resource = $entry->getResource()) {
            return $this->voter->isAccessGranted($resource, 'VIEW');
        }

        return true;
    }

    /**
     * Appends a breadcrumb element.
     *
     * @param string $name
     * @param string $label
     * @param string $route
     * @param array $parameters
     */
    public function breadcrumbAppend($name, $label, $route = null, array $parameters = array())
    {
        $this->createBreadcrumb();

        $this
            ->breadcrumb
            ->addChild($name, array('route' => $route, 'routeParameters' => $parameters))
            ->setLabel($label)
        ;
    }

    /**
     * Create if not exists and returns the breadcrumb.
     *
     * @return \Knp\Menu\ItemInterface
     */
    public function createBreadcrumb()
    {
        if (null === $this->breadcrumb) {
            $this->breadcrumb = $this->factory->createItem('root', array(
                'childrenAttributes' => array(
                    'class' => 'breadcrumb hidden-xs'
                )
            ));
            $this->breadcrumb->addChild('dashboard', array('route' => 'ekyna_admin_dashboard'))->setLabel('ekyna_admin.dashboard');
        }
        return $this->breadcrumb;
    }

    /**
     * Translate label.
     *
     * @param string $label
     * @param array  $parameters
     *
     * @return string
     */
    private function translate($label, $parameters = array(), $domain = null)
    {
        return $this->translator->trans($label, $parameters, $domain);
    }
}
