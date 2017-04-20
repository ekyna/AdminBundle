<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\Resource\Manager\ResourceManagerInterface;
use Ekyna\Component\User\Service\SecurityUtil;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractUserCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractUserCommand extends Command
{
    protected const PASSWORD_REGEX = '#^[\S]{6,}$#';

    protected UserRepositoryInterface  $userRepository;
    protected ResourceManagerInterface $userManager;
    protected SecurityUtil             $securityUtil;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface  $userRepository
     * @param ResourceManagerInterface $userManager
     * @param SecurityUtil             $securityUtil
     */
    public function __construct(
        UserRepositoryInterface $userRepository,
        ResourceManagerInterface $userManager,
        SecurityUtil $securityUtil
    ) {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->userManager = $userManager;
        $this->securityUtil = $securityUtil;
    }
}
