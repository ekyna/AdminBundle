<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

use Knp\Menu\ItemInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Main menu builder.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class BackendMenuBuilder extends MenuBuilder
{
    /**
     * @var \Knp\Menu\ItemInterface
     */
    private $breadcrumb;

    /**
     * Builds backend sidebar menu.
     *
     * @param Request $request
     *
     * @return ItemInterface
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

    protected function appendChilds($menu, $childOptions)
    {
        foreach ($this->pool->getGroups() as $group) {
            
            $groupOptions = array(
                'labelAttributes' => array('icon' => $group->getIcon()),
                'childrenAttributes' => array('class' => 'submenu')
            );
            if ($group->hasEntries()) {
                $groupOptions['labelAttributes']['class'] = 'dropdown-toggle';
            } else {
                $groupOptions['route'] = $group->getRoute();
            }
            $child = $menu
                ->addChild($group->getName(), $groupOptions)
                ->setLabel($this->translate($group->getLabel(), array(), $group->getDomain()))
            ;
            if ($group->hasEntries()) {
                foreach ($group->getEntries() as $entry) {
                    $child->addChild($entry->getName(), array(
                        'route' => $entry->getRoute()
                    ))->setLabel($this->translate($entry->getLabel(), array(), $entry->getDomain()));
                }
            }
        }
    }

    public function breadcrumbAppend($name, $label, $route = null, array $parameters = array())
    {
        $this->createBreadcrumb();

        $this
            ->breadcrumb
            ->addChild($name, array('route' => $route, 'routeParameters' => $parameters))
            ->setLabel($label);
    }

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
}
