<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

class MenuEntry
{
    /**
     * Menu entrey name
     * 
     * @var string
     */
    private $name;

    /**
     * Menu entry route name
     * 
     * @var string
     */
    private $route;

    /**
     * Menu entry label
     * 
     * @var string
     */
    private $label;

    /**
     * Translation domain
     * 
     * @var string
     */
    private $domain;


    /**
     * Create a backend menu entry
     *  
     * @param string $name
     * @param string $route
     * @param string $label
     * @param string $domain
     */
    public function __construct($name, $route, $label,  $domain = null)
    {
        $this
            ->setName($name)
            ->setRoute($route)
            ->setLabel($label, $domain)
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
     * @return MenuEntry
     */
    public function setName($name)
    {
        $this->name = $name;
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
     * @return MenuEntry
     */
    public function setRoute($route)
    {
        $this->route = $route;
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
     * @return MenuEntry
     */
    public function setLabel($label, $domain = null)
    {
        $this->label = $label;
        $this->setDomain($domain);
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
     * @return MenuEntry
     */
    public function setDomain($domain)
    {
        if (strlen($domain) > 0)
            $this->domain = $domain;
        return $this;
    }
}
