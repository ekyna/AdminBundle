<?php

namespace Ekyna\Bundle\AdminBundle\Command;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Component\Resource\Operator\ResourceOperatorInterface;
use Symfony\Component\Console\Command\Command;

/**
 * Class AbstractUserCommand
 * @package Ekyna\Bundle\AdminBundle\Command
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractUserCommand extends Command
{
    protected const PASSWORD_REGEX = '#^[\S]{6,}$#';

    /**
     * @var UserRepositoryInterface
     */
    protected $userRepository;

    /**
     * @var ResourceOperatorInterface
     */
    protected $userOperator;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface   $userRepository
     * @param ResourceOperatorInterface $userOperator
     */
    public function __construct(UserRepositoryInterface $userRepository, ResourceOperatorInterface $userOperator)
    {
        parent::__construct();

        $this->userRepository = $userRepository;
        $this->userOperator = $userOperator;
    }
}
