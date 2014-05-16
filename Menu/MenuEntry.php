<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

/**
 * MenuEntry.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class MenuEntry
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $route;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $domain;

    /**
     * @var integer
     */
    private $position;

    /**
     * @var string
     */
    private $resource;


    /**
     * Creates a backend menu entry.
     *  
     * @param array $options
     */
    public function __construct($options)
    {
        $this
            ->setName($options['name'])
            ->setRoute($options['route'])
            ->setLabel($options['label'], $options['domain'])
            ->setPosition($options['position'])
            ->setResource($options['resource'])
        ;
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     * 
     * @return MenuEntry
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the route name.
     * 
     * @return string
     */
    public function getRoute()
    {
        return $this->route;
    }

    /**
     * Sets the route name.
     * 
     * @param string $route
     * 
     * @return MenuEntry
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Returns the label.
     * 
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * Sets the label.
     * 
     * @param string $label
     * @param string $domain
     * 
     * @return MenuEntry
     */
    public function setLabel($label, $domain = null)
    {
        $this->label = $label;
        $this->setDomain($domain);

        return $this;
    }

    /**
     * Returns the translation domain.
     * 
     * @return string
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Sets the translation domain.
     * 
     * @param string $domain
     * 
     * @return MenuEntry
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

	/**
	 * Returns the position.
	 * 
     * @return integer
     */
    public function getPosition()
    {
        return $this->position;
    }

	/**
	 * Sets the position.
	 * 
     * @param integer $position
     * 
     * @return MenuEntry
     */
    public function setPosition($position)
    {
        $this->position = $position;

        return $this;
    }

	/**
	 * Returns the resource.
	 * 
     * @return string
     */
    public function getResource()
    {
        return $this->resource;
    }

	/**
	 * Sets the resource.
	 * 
     * @param string $resource
     * 
     * @return MenuEntry
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

}
