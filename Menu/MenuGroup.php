<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

/**
 * Class MenuGroup
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class MenuGroup
{
    /**
     * @var string
     */
    private $name;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $icon;

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
    private $route;

    /**
     * @var array
     */
    private $entries;

    /**
     * Preparation flag
     * @var boolean
     */
    private $prepared;


    /**
     * Creates a backend menu group.
     * 
     * @param array $options
     */
    public function __construct($options)
    {
        $this->entries = array();
        $this->prepared = false;

        $this
            ->setName($options['name'])
            ->setLabel($options['label'], $options['domain'])
            ->setIcon($options['icon'])
            ->setPosition($options['position'])
            ->setRoute($options['route'])
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
     * @return MenuGroup
     */
    public function setName($name)
    {
        $this->name = $name;

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
     * @return MenuGroup
     */
    public function setLabel($label, $domain = null)
    {
        $this->label = $label;
        $this->setDomain($domain);

        return $this;
    }

    /**
     * Returns the icon.
     *
     * @return string
     */
    public function getIcon()
    {
        return $this->icon;
    }

    /**
     * Sets the icon.
     *
     * @param string $icon
     * 
     * @return MenuGroup
     */
    public function setIcon($icon)
    {
        $this->icon = $icon;

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
     * @return MenuGroup
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
     * @return MenuGroup
     */
    public function setPosition($position)
    {
        $this->position = $position;

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
     * @return MenuGroup
     */
    public function setRoute($route)
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Returns the entries.
     * 
     * @return MenuEntry[]
     */
    public function getEntries()
    {
        return $this->entries;
    }

    /**
     * Adds an entry.
     * 
     * @param MenuEntry $entry
     * @throws \RuntimeException
     * @return MenuGroup
     */
    public function addEntry(MenuEntry $entry)
    {
        if ($this->prepared) {
            throw new \RuntimeException('MenuGroup has been prepared and can\'t receive new entries.');
        }
        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Returns whether the group has entries or not.
     * 
     * @return boolean
     */
    public function hasEntries()
    {
        return (bool) 0 < count($this->entries);
    }

    /**
     * Prepares the group for rendering.
     */
    public function prepare()
    {
        if ($this->prepared) {
            return;
        }
        usort($this->entries, function(MenuEntry $a, MenuEntry $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return 0;
            }
            return $a->getPosition() > $b->getPosition() ? 1 : -1;
        });
        $this->prepared = true;
    }
}
