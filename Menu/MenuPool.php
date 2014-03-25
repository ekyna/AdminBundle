<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

use \RuntimeException;

class MenuPool
{
    /**
     * Menu groups
     * @var array
     */
    private $groups;

    /**
     * Preparation flag
     * 
     * @var boolean
     */
    private $prepared;

    public function __construct()
    {
        $this->groups = array();
        $this->prepared = false;
    }

    /**
     * Create a menu group
     * 
     * @param string $name
     * @param string $label
     * @param string $icon
     * @param string $domain
     */
    public function createGroupReference($name, $label, $icon, $domain = null, $order = 1, $route = null)
    {
        $group = new MenuGroup($name, $label, $icon, $domain, $order, $route);
        $this->addGroup($group);
    }

    /**
     * Create a menu entry
     * 
     * @param string $group_name
     * @param string $route
     * @param string $label
     * @param string $domain
     * @throws RuntimeException
     */
    public function createEntryReference($group_name, $name, $route, $label, $domain = null)
    {
        if($this->hasGroup($group_name)) {
            $group = $this->getGroup($group_name);
            $entry = new MenuEntry($name, $route, $label, $domain);
            $group->addEntry($entry);
        }else{
            throw new RuntimeException('Menu Group "'.$group_name.'" not found.');
        }
    }

    /**
     * Add group to menu
     * 
     * @param MenuGroup $group
     */
    private function addGroup(MenuGroup $group)
    {
        if(!$this->hasGroup($group->getName())) {
            $this->groups[$group->getName()] = $group;
        }
    }

    /**
     * Check if menu group is allready defined
     * 
     * @param string $group_name
     * @return boolean
     */
    private function hasGroup($group_name)
    {
        return (bool) isset($this->groups[$group_name]);
    }

    /**
     * Get a menu group by his name
     * 
     * @param string $group_name
     * @return MenuGroup
     */
    private function getGroup($group_name)
    {
        if($this->hasGroup($group_name))
            return $this->groups[$group_name];

        return false;
    }

    /**
     * Get menu groups
     * 
     * @return multitype:MenuGroup
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Prepare the pool for menu render
     */
    public function prepare()
    {
        if($this->prepared) return;
        usort($this->groups, function(MenuGroup $a, MenuGroup $b) {
            if($a->getOrder() == $b->getOrder()) return 0;
            return $a->getOrder() > $b->getOrder() ? 1 : -1;
        });
        $this->prepared = true;
    }
}