<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use DateTime;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Component\User\Service\Security\SecurityUtil;

use function time;

/**
 * Class ApiTokenGenerator
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiTokenGenerator
{
    private SecurityUtil $utils;


    public function __construct(SecurityUtil $utils)
    {
        $this->utils = $utils;
    }

    public function generate(UserInterface $user): bool
    {
        $expiresAt = $user->getApiExpiresAt();

        if ($expiresAt && $expiresAt->getTimestamp() > time()) {
            return false;
        }

        $user
            ->setApiToken($this->utils->generateToken())
            ->setApiExpiresAt(new DateTime('+1 hour')); // TODO Make configurable

        return true;
    }
}
