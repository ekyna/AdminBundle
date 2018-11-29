<?php

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

/**
 * Class SecurityUtil
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityUtil
{
    /**
     * Generate a new user password.
     *
     * @param UserInterface $user
     *
     * @return string The generated password.
     */
    static public function generatePassword(UserInterface $user)
    {
        $password = bin2hex(random_bytes(4));

        $user->setPlainPassword($password);

        return $password;
    }
}
