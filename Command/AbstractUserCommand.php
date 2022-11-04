<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\User\Service\Security\SecurityUtil;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractUserCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractUserCommand extends Command
{
    protected const PASSWORD_REGEX = '#^[\S]{6,}$#';

    public function __construct(
        protected readonly UserRepositoryInterface  $userRepository,
        protected readonly ResourceManagerInterface $userManager,
        protected readonly SecurityUtil             $securityUtil
    ) {
        parent::__construct();
    }
}
