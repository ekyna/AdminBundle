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

    public function __construct(array $options)
    {
        $this
            ->setName($options['name'])
            ->setLabel($options['label'], $options['domain'])
            ->setIcon($options['icon'])
            ->setPosition($options['position'])
            ->setRoute($options['route']);
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getLabel(): string
    {
        return $this->label;
    }

    public function setLabel(string $label, string $domain = null): self
    {
        $this->label = $label;

        $this->setDomain($domain);

        return $this;
    }

    public function getIcon(): string
    {
        return $this->icon;
    }

    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * Returns the translation domain.
     */
    public function getDomain(): ?string
    {
        return $this->domain;
    }

    /**
     * Sets the translation domain.
     */
    public function setDomain(?string $domain): self
    {
        $this->domain = $domain;

        return $this;
    }

    public function getPosition(): int
    {
        return $this->position;
    }

    public function setPosition(int $position): self
    {
        $this->position = $position;

        return $this;
    }

    /**
     * Returns the route name.
     */
    public function getRoute(): ?string
    {
        return $this->route;
    }

    /**
     * Sets the route name.
     */
    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getEntries(): array
    {
        return $this->entries;
    }

    /**
     * Adds an entry.
     *
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
