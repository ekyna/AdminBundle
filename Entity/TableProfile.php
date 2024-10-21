<?php

declare(strict_types=1);

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
    private ?int           $id        = null;
    private ?UserInterface $user      = null;
    private ?string        $tableHash = null;
    private ?string        $name      = null;
    private array          $data;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->data = [];
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @inheritDoc
     */
    public function getKey(): string
    {
        return (string)$this->id;
    }

    /**
     * Sets the user.
     *
     * @param UserInterface $user
     *
     * @return TableProfile
     */
    public function setUser(UserInterface $user): TableProfile
    {
        $this->user = $user;

        return $this;
    }

    /**
     * Returns the user.
     *
     * @return UserInterface|null
     */
    public function getUser(): ?UserInterface
    {
        return $this->user;
    }

    /**
     * Sets the table hash.
     *
     * @param string $hash
     *
     * @return TableProfile
     */
    public function setTableHash(string $hash): TableProfile
    {
        $this->tableHash = $hash;

        return $this;
    }

    /**
     * Returns the table hash.
     *
     * @return string
     */
    public function getTableHash(): string
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
    public function setName(string $name): TableProfile
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Sets the profile data.
     *
     * @param array $data
     *
     * @return ProfileInterface
     */
    public function setData(array $data): ProfileInterface
    {
        $this->data = $data;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getData(): array
    {
        return $this->data;
    }
}
