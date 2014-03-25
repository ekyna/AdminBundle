<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

class MenuGroup
{
    /**
     * Menu group name
     * 
     * @var string
     */
    private $name;

    /**
     * Menu group label
     * 
     * @var string
     */
    private $label;

    /**
     * Icon
     *
     * @var string
     */
    private $icon;

    /**
     * Translation domain
     * 
     * @var string
     */
    private $domain;

    /**
     * Menu group order
     * 
     * @var integer
     */
    private $order;

    /**
     * Menu group route
     * 
     * @var string
     */
    private $route;
    
    /**
     * Menu group entries
     * 
     * @var array
     */
    private $entries;
    
    /**
     * Create a backend menu group
     * 
     * @param string $name
     * @param string $label
     * @param string $icon
     * @param integer $order
     * @param string $domain
     */
    public function __construct($name, $label, $icon, $domain = null, $order = 1, $route = null)
    {
        $this
            ->setName($name)
            ->setLabel($label, $domain)
            ->setIcon($icon)
            ->setOrder($order)
            ->setRoute($route)
        ;
    }
    
    /**
     * Get name
     * 
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set name
     * 
     * @param string $name
     * @return MenuGroup
     */
    public function setName($name)
    {
        $this->name = $name;
        return $this;
    }

    /**
     * Get label
     * 
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Set label
     * 
     * @param string $label
     * @param string $domain
     * @return MenuGroup
     */
    public function setLabel($label, $domain = null)
    {
        $this->label = $label;
        $this->setDomain($domain);
        return $this;
    }

    /**
     * Get entry icon
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }
    
    /**
     * Set entry icon
     *
     * @param string $icon
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;
        return $this;
    }

    /**
     * Get translation domain
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Set translation domain
     * 
     * @param string $domain
     * @return MenuGroup
     */
    public function setDomain($domain)
    {
        if(strlen($domain) > 0) $this->domain = $domain;
        return $this;
    }

    /**
     * Get order
     *
     * @return integer
     */
    public function getOrder()
    {
        return $this->order;
    }
    
    /**
     * Set order
     *
     * @param integer $order
     * @return MenuGroup
     */
    public function setOrder($order)
    {
        $this->order = $order;
        return $this;
    }
    
    
    /**
     * Get route name
     *
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }
    
    /**
     * Set route name
     *
     * @param string $route
     * @return MenuGroup
     */
    public function setRoute($route)
    {
        $this->route = $route;
        return $this;
    }

    /**
     * Get entries
     * 
     * @return multitype:MenuEntry
     */
    public function getEntries()
    {
        return $this->entries;
    }
    
    /**
     * Add entry
     * 
     * @param MenuEntry $entry
     * @return MenuGroup
     */
    public function addEntry(MenuEntry $entry)
    {
        $this->entries[] = $entry;
        return $this;
    }

    /**
     * Has entries
     * 
     * @return boolean
     */
    public function hasEntries()
    {
        return (bool) count($this->entries) > 0;
    }
}
