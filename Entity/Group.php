<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Entity;

use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Bundle\ResourceBundle\Model\AclSubjectTrait;
use Ekyna\Component\Resource\Model\AbstractResource;
use Ekyna\Component\Resource\Model\SortableTrait;

/**
 * Class UserGroup
 * @package Ekyna\Bundle\AdminBundle\Entity
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Group extends AbstractResource implements GroupInterface
{
    use AclSubjectTrait;
    use SortableTrait;

    private ?string $name  = null;
    private array   $roles = [];


    /**
     * Returns the string representation.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->name ?: 'New group';
    }

    /**
     * @inheritDoc
     */
    public function getName(): ?string
    {
        return $this->name;
    }

    /**
     * @inheritDoc
     */
    public function setName(string $name): GroupInterface
    {
        $this->name = $name;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getRoles(): array
    {
        return $this->roles;
    }

    /**
     * @inheritDoc
     */
    public function setRoles(array $roles): GroupInterface
    {
        $this->roles = $roles;

        return $this;
    }
}
