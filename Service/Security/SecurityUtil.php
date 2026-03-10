<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;

use function in_array;

/**
 * Class SecurityUtil
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class SecurityUtil
{
    public static function isPasswordGenerationAllowed(UserInterface $user): bool
    {
        if (!$user->isEnabled()) {
            return false;
        }

        if (in_array('ROLE_SUPER_ADMIN', $user->getGroup()->getRoles())) {
            return false;
        }

        return true;
    }
}
