<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Menu;

use LogicException;

use function count;
use function usort;

/**
 * Class MenuGroup
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MenuGroup
{
    private string  $name;
    private string  $label;
    private ?string $icon     = null;
    private ?string $domain   = null;
    private int     $position = 0;
    private ?string $route    = null;

    private array $entries  = [];
    private bool  $prepared = false;


    /**
     * Creates a backend menu group.
     *
     * @param array $options
     */
    public function __construct(array $options)
    {
        $this
            ->setName($options['name'])
            ->setLabel($options['label'], $options['domain'])
            ->setIcon($options['icon'])
            ->setPosition($options['position'])
            ->setRoute($options['route']);
    }

    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName(): string
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
    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Returns the label.
     *
     * @return string
     */
    public function getLabel(): string
    {
        return $this->label;
    }

    /**
     * Sets the label.
     *
     * @param string      $label
     * @param string|null $domain
     *
     * @return MenuGroup
     */
    public function setLabel(string $label, string $domain = null): self
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
    public function getIcon(): string
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
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Returns the translation domain.
     *
     * @return string
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Sets the translation domain.
     *
     * @param string|null $domain
     *
     * @return MenuGroup
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Returns the position.
     *
     * @return int
     */
    public function getPosition(): int
    {
        return $this->position;
    }

    /**
     * Sets the position.
     *
     * @param int $position
     *
     * @return MenuGroup
     */
    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Returns the route name.
     *
     * @return string|null
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Sets the route name.
     *
     * @param string|null $route
     *
     * @return MenuGroup
     */
    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    /**
     * Returns the entries.
     *
     * @return MenuEntry[]
     */
    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Adds an entry.
     *
     * @param MenuEntry $entry
     *
     * @return MenuGroup
     * @throws LogicException
     */
    public function addEntry(MenuEntry $entry): self
    {
        if ($this->prepared) {
            throw new LogicException('MenuGroup has been prepared and can\'t receive new entries.');
        }

        $this->entries[] = $entry;

        return $this;
    }

    /**
     * Returns whether the group has entries or not.
     *
     * @return bool
     */
    public function hasEntries(): bool
    {
        return 0 < count($this->entries);
    }

    /**
     * Prepares the group for rendering.
     */
    public function prepare(): void
    {
        if ($this->prepared) {
            return;
        }

        usort($this->entries, function (MenuEntry $a, MenuEntry $b): int {
            if ($a->getPosition() == $b->getPosition()) {
                return 0;
            }

            return $a->getPosition() > $b->getPosition() ? 1 : -1;
        });

        $this->prepared = true;
    }
}
