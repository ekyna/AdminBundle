<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Model;

use Ekyna\Bundle\ResourceBundle\Model\AclSubjectInterface;
use Ekyna\Component\Resource\Model as RM;

/**
 * Interface GroupInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GroupInterface extends RM\ResourceInterface, AclSubjectInterface, RM\SortableInterface
{
    /**
     * Returns the name.
     *
     * @return string|null
     */
    public function getName(): ?string;

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|GroupInterface
     */
    public function setName(string $name): GroupInterface;

    /**
     * Returns the roles.
     *
     * @return array
     */
    public function getRoles(): array;

    /**
     * Sets the roles.
     *
     * @param array $roles
     *
     * @return $this|GroupInterface
     */
    public function setRoles(array $roles): GroupInterface;
}
