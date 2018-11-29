<?php

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Symfony\Component\Security\Core\User\UserProviderInterface as SymfonyProvider;

/**
 * Interface UserProviderInterface
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface UserProviderInterface extends SymfonyProvider
{
    /**
     * Returns whether a user is available or not.
     *
     * @return bool
     */
    public function hasUser();

    /**
     * Returns the current user.
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\UserInterface|null
     */
    public function getUser();

    /**
     * Resets the user provider.
     */
    public function reset();

    /**
     * Finds one user for the given email.
     *
     * @param string $email
     * @param bool   $throwException
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\UserInterface|null
     */
    public function findUserByEmail(string $email, bool $throwException = true);
}
