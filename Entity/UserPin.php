<?php

namespace Ekyna\Bundle\AdminBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Class UserPin
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserPin
{
    /**
     * @var int
     */
    private $id;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var string
     */
    private $path;

    /**
     * @var string
     */
    private $label;

    /**
     * @var string
     */
    private $resource;

    /**
     * @var string
     */
    private $identifier;

    /**
     * @var \DateTime
     */
    private $createdAt;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->createdAt = new \DateTime();
    }

    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString()
    {
        return $this->label;
    }

    /**
     * Returns the id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface
     */
    public function getUser()
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
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the path.
     *
     * @return string
     */
    public function getPath()
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
    public function setPath($path)
    {
        $this->path = $path;

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
     *
     * @return UserPin
     */
    public function setLabel($label)
    {
        $this->label = $label;

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
     * @return UserPin
     */
    public function setResource($resource)
    {
        $this->resource = $resource;

        return $this;
    }

    /**
     * Returns the identifier.
     *
     * @return string
     */
    public function getIdentifier()
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
    public function setIdentifier($identifier)
    {
        $this->identifier = $identifier;

        return $this;
    }

    /**
     * Returns the "created at" date.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Sets the "created at" date.
     *
     * @param \DateTime $createdAt
     *
     * @return UserPin
     */
    public function setCreatedAt(\DateTime $createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Converts to an array.
     *
     * @return array
     */
    public function toArray()
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
