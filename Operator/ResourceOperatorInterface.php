<?php

namespace Ekyna\Bundle\AdminBundle\Operator;

use Ekyna\Bundle\AdminBundle\Event\ResourceEvent;

/**
 * Interface ResourceOperatorInterface
 * @package Ekyna\Bundle\AdminBundle\Doctrine\ORM
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceOperatorInterface
{
    /**
     * Creates the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     *
     * @return ResourceEvent
     */
    public function create($resourceOrEvent);

    /**
     * Updates the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     *
     * @return ResourceEvent
     */
    public function update($resourceOrEvent);

    /**
     * Deletes the resource.
     *
     * @param object|ResourceEvent $resourceOrEvent
     * @param boolean $hard Whether to bypass soft deleteable behavior or not.
     *
     * @return ResourceEvent
     */
    public function delete($resourceOrEvent, $hard = false);
}
