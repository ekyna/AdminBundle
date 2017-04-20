<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Entity;

use DateTime;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Class UserPin
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserPin
{
    private ?int           $id         = null;
    private ?UserInterface $user       = null;
    private ?string        $path       = null;
    private ?string        $label      = null;
    private ?string        $resource   = null;
    private ?string        $identifier = null;
    private DateTime       $createdAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new DateTime();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->label ?: 'New user pin';
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return UserPin
     */
    public function setUser(UserInterface $user): UserPin
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath(): ?string
    {
        return $this->path;
    }

    /**
     * Sets the path.
     *
     * @param string $path
     *
     * @return UserPin
     */
    public function setPath(string $path): UserPin
    {
        $this->path = $path;

        return $this;
    }

    /**
     * Returns the label.
     *
     * @return string
     */
    public function getLabel(): ?string
    {
        return $this->label;
    }

    /**
     * Sets the label.
     *
     * @param string $label
     *
     * @return UserPin
     */
    public function setLabel(string $label): UserPin
    {
        $this->label = $label;

        return $this;
    }

    /**
     * Returns the resource.
     *
     * @return string
     */
    public function getResource(): ?string
    {
        return $this->resource;
    }

    /**
     * Sets the resource.
     *
     * @param string $resource
     *
     * @return UserPin
     */
    public function setResource(string $resource): UserPin
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier(): ?string
    {
        return $this->identifier;
    }

    /**
     * Sets the identifier.
     *
     * @param string $identifier
     *
     * @return UserPin
     */
    public function setIdentifier(string $identifier): UserPin
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns the "created at" date.
     *
     * @return DateTime
     */
    public function getCreatedAt(): DateTime
    {
        return $this->createdAt;
    }

    /**
     * Sets the "created at" date.
     *
     * @param DateTime $date
     *
     * @return UserPin
     */
    public function setCreatedAt(DateTime $date): UserPin
    {
        $this->createdAt = $date;

        return $this;
    }

    /**
     * Converts to an array.
     *
     * @return array
     */
    public function toArray(): array
    {
        return [
            'id'         => $this->getId(),
            'path'       => $this->getPath(),
            'label'      => $this->getLabel(),
            'resource'   => $this->getResource(),
            'identifier' => $this->getIdentifier(),
        ];
    }
}
