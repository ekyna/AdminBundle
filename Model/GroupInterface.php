<?php

namespace Ekyna\Bundle\AdminBundle\Model;

use Ekyna\Component\Resource\Model as RM;

/**
 * Interface GroupInterface
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface GroupInterface extends RM\ResourceInterface, RM\SortableInterface
{
    /**
     * Returns the name.
     *
     * @return string
     */
    public function getName();

    /**
     * Sets the name.
     *
     * @param string $name
     *
     * @return $this|GroupInterface
     */
    public function setName($name);

    /**
     * Returns the roles.
     *
     * @return array
     */
    public function getRoles();

    /**
     * Sets the roles.
     *
     * @param array $roles
     *
     * @return $this|GroupInterface
     */
    public function setRoles(array $roles);
}
