<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Menu;

/**
 * Class MenuItem
 * @package Ekyna\Bundle\AdminBundle\Menu
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class MenuEntry
{
    private string $name;
    private ?string $label = null;
    private ?string $domain = null;
    private int $position;
    private ?string $route = null;
    private ?string $resource = null;
    private ?string $action = null;
    private ?string $permission = null;

    public function __construct(array $options)
    {
        $this
            ->setName($options['name'])
            ->setLabel($options['label'])
            ->setDomain($options['domain'])
            ->setPosition($options['position'])
            ->setRoute($options['route'])
            ->setResource($options['resource'])
            ->setAction($options['action'])
            ->setPermission($options['permission']);
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

    public function getLabel(): ?string
    {
        return $this->label;
    }

    public function setLabel(?string $label, string $domain = null): self
    {
        $this->label = $label;

        $this->setDomain($domain);

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

    public function getRoute(): ?string
    {
        return $this->route;
    }

    public function setRoute(?string $route): self
    {
        $this->route = $route;

        return $this;
    }

    public function getResource(): ?string
    {
        return $this->resource;
    }

    public function setResource(?string $resource): self
    {
        $this->resource = $resource;

        return $this;
    }

    public function getAction(): ?string
    {
        return $this->action;
    }

    public function setAction(?string $action): self
    {
        $this->action = $action;

        return $this;
    }

    public function getPermission(): ?string
    {
        return $this->permission;
    }

    public function setPermission(?string $permission): self
    {
        $this->permission = $permission;

        return $this;
    }
}
