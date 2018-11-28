<?php

namespace Ekyna\Bundle\AdminBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class UserGroup
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Group implements GroupInterface
{
    use SortableTrait;

    /**
     * @var int
     */
    private $id;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $roles;


    /**
     * @inheritdoc
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @inheritdoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @inheritdoc
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getRoles()
    {
        return $this->roles;
    }

    /**
     * @inheritdoc
     */
    public function setRoles(array $roles)
    {
        $this->roles = $roles;

        return $this;
    }
}