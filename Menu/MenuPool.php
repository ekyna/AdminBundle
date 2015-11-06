<?php

namespace Ekyna\Bundle\AdminBundle\Menu;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MenuPool
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class MenuPool
{
    /**
     * Menu groups
     * @var \Ekyna\Bundle\AdminBundle\Menu\MenuGroup[]
     */
    private $groups;

    /**
     * Preparation flag
     * @var boolean
     */
    private $prepared;

    /**
     * @var OptionsResolver
     */
    private $groupOptionsResolver;

    /**
     * @var OptionsResolver
     */
    private $entryOptionsResolver;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->groups = [];
        $this->prepared = false;

        $this->initResolvers();
    }

    /**
     * Initializes the options resolvers.
     */
    private function initResolvers()
    {
        $this->groupOptionsResolver = new OptionsResolver();
        $this->groupOptionsResolver
            ->setDefaults([
            	'name'     => null,
            	'label'    => null,
            	'icon'     => null,
            	'position' => 1,
            	'domain'   => 'messages',
            	'route'    => null,
            ])
            ->setAllowedTypes('name',     'string')
            ->setAllowedTypes('label',    'string')
            ->setAllowedTypes('icon',     'string')
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('domain',   'string')
            ->setAllowedTypes('route',    ['string', 'null'])
        ;

        $this->entryOptionsResolver = new OptionsResolver();
        $this->entryOptionsResolver
            ->setDefaults([
            	'name'     => null,
            	'label'    => null,
            	'route'    => null,
            	'position' => 1,
                'domain'   => 'messages',
            	'resource' => null,
            ])
            ->setAllowedTypes('name',     'string')
            ->setAllowedTypes('label',    'string')
            ->setAllowedTypes('route',    'string')
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('domain',   'string')
            ->setAllowedTypes('resource', 'string')
        ;
    }

    /**
     * Creates a menu group.
     * 
     * @param array $options
     * 
     * @throws \RuntimeException
     */
    public function createGroup(array $options)
    {
        if ($this->prepared) {
            throw new \RuntimeException('MenuPool has been prepared and can\'t receive new groups.');
        }

        $group = new MenuGroup($this->groupOptionsResolver->resolve($options));

        $this->addGroup($group);
    }

    /**
     * Creates a menu entry.
     * 
     * @param string $group_name
     * @param array $options
     * 
     * @throws \RuntimeException
     */
    public function createEntry($group_name, array $options)
    {
        if (! $this->hasGroup($group_name)) {
            throw new \RuntimeException('Menu Group "'.$group_name.'" not found.');
        }

        $entry = new MenuEntry($this->entryOptionsResolver->resolve($options));

        $group = $this->getGroup($group_name);
        $group->addEntry($entry);
    }

    /**
     * Add group to menu
     * 
     * @param MenuGroup $group
     */
    private function addGroup(MenuGroup $group)
    {
        if (!$this->hasGroup($group->getName())) {
            $this->groups[$group->getName()] = $group;
        }
    }

    /**
     * Check if menu group is allready defined
     * 
     * @param string $group_name
     * 
     * @return boolean
     */
    private function hasGroup($group_name)
    {
        return array_key_exists($group_name, $this->groups);
    }

    /**
     * Get a menu group by his name
     * 
     * @param string $group_name
     * 
     * @return MenuGroup
     */
    private function getGroup($group_name)
    {
        if ($this->hasGroup($group_name)) {
            return $this->groups[$group_name];
        }

        return false;
    }

    /**
     * Returns the menu groups.
     * 
     * @return MenuGroup[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * Prepares the pool for rendering.
     */
    public function prepare()
    {
        if ($this->prepared) {
            return;
        }
        usort($this->groups, function(MenuGroup $a, MenuGroup $b) {
            if ($a->getPosition() == $b->getPosition()) {
                return 0;
            }
            return $a->getPosition() > $b->getPosition() ? 1 : -1;
        });
        foreach ($this->groups as $group) {
            $group->prepare();
        }
        $this->prepared = true;
    }
}
