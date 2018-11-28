<?php

namespace Ekyna\Bundle\AdminBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\Table\Context\Profile\ProfileInterface;

/**
 * Class TableProfile
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TableProfile implements ProfileInterface
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
    private $tableHash;

    /**
     * @var string
     */
    private $name;

    /**
     * @var array
     */
    private $data;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->data = [];
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
     * @inheritDoc
     */
    public function getKey()
    {
        return $this->id;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return TableProfile
     */
    public function setUser(UserInterface $user)
    {
        $this->user = $user;

        return $this;
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
     * @inheritDoc
     */
    public function setTableHash($hash)
    {
        $this->tableHash = $hash;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getTableHash()
    {
        return $this->tableHash;
    }

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return TableProfile
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Sets the data.
     *
     * @param array $data
     *
     * @return TableProfile
     */
    public function setData(array $data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData()
    {
        return $this->data;
    }
}
