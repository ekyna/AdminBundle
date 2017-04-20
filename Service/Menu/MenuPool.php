<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Menu;

use LogicException;
use RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Contracts\Translation\TranslatableInterface;

use function array_key_exists;
use function usort;

/**
 * Class MenuPool
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MenuPool
{
    /** @var array<MenuGroup> */
    private array $groups   = [];
    private bool  $prepared = false;

    private OptionsResolver $groupOptionsResolver;
    private OptionsResolver $entryOptionsResolver;

    public function __construct()
    {
        $this->initResolvers();
    }

    private function initResolvers(): void
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
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('label', ['string', TranslatableInterface::class])
            ->setAllowedTypes('icon', 'string')
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('domain', ['null', 'string'])
            ->setAllowedTypes('route', ['null', 'string']);

        $this->entryOptionsResolver = new OptionsResolver();
        $this->entryOptionsResolver
            ->setDefaults([
                'name'       => null,
                'label'      => null,
                'domain'     => null,
                'position'   => 1,
                'route'      => null,
                'resource'   => null,
                'action'     => null,
                'permission' => null,
            ])
            ->setAllowedTypes('name', 'string')
            ->setAllowedTypes('label', ['null', 'string', TranslatableInterface::class])
            ->setAllowedTypes('domain', ['null', 'string'])
            ->setAllowedTypes('position', 'int')
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('resource', ['null', 'string'])
            ->setAllowedTypes('action', ['null', 'string'])
            ->setAllowedTypes('permission', ['null', 'string']);
    }

    /**
     * Creates a menu group.
     *
     * @throws LogicException
     */
    public function createGroup(array $options): self
    {
        if ($this->prepared) {
            throw new LogicException('MenuPool has been prepared and can\'t receive new groups.');
        }

        $group = new MenuGroup($this->groupOptionsResolver->resolve($options));

        $this->addGroup($group);

        return $this;
    }

    /**
     * Creates a menu entry.
     *
     * @throws RuntimeException
     */
    public function createEntry(string $groupName, array $options): self
    {
        if (!$this->hasGroup($groupName)) {
            throw new RuntimeException('Menu Group "' . $groupName . '" not found.');
        }

        $entry = new MenuEntry($this->entryOptionsResolver->resolve($options));

        if (null !== $group = $this->getGroup($groupName)) {
            $group->addEntry($entry);
        }

        return $this;
    }

    private function addGroup(MenuGroup $group): void
    {
        if ($this->hasGroup($group->getName())) {
            return;
        }

        $this->groups[$group->getName()] = $group;
    }

    /**
     * Returns whether menu group is already defined.
     */
    private function hasGroup(string $groupName): bool
    {
        return array_key_exists($groupName, $this->groups);
    }

    private function getGroup(string $groupName): ?MenuGroup
    {
        if ($this->hasGroup($groupName)) {
            return $this->groups[$groupName];
        }

        return null;
    }

    /**
     * @return array<MenuGroup>
     */
    public function getGroups(): array
    {
        return $this->groups;
    }

    /**
     * Prepares the pool for rendering.
     */
    public function prepare(): void
    {
        if ($this->prepared) {
            return;
        }

        usort($this->groups, function (MenuGroup $a, MenuGroup $b): int {
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
