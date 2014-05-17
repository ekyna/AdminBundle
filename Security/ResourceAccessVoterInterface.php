<?php

namespace Ekyna\Bundle\AdminBundle\Security;

/**
 * ResourceAccessVoterInterface.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
interface ResourceAccessVoterInterface
{
    /**
     * Returns whether the current user is granted for the given permission on the given resource.
     *
     * @param mixed  $resource
     * @param string $permission
     *
     * @throws \InvalidArgumentException
     *
     * @return boolean
     */
    public function isAccessGranted($resource, $permission);
}
