<?php

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\UserInterface as SymfonyUser;

/**
 * Class UserProvider
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserProvider implements UserProviderInterface
{
    /**
     * @var UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserInterface
     */
    private $user;

    /**
     * @var bool
     */
    private $initialized = false;


    /**
     * Constructor.
     *
     * @param UserRepositoryInterface $userRepository
     * @param TokenStorageInterface   $tokenStorage
     */
    public function __construct(UserRepositoryInterface $userRepository, TokenStorageInterface $tokenStorage)
    {
        $this->userRepository = $userRepository;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @inheritdoc
     */
    public function loadUserByUsername($email)
    {
        return $this->findUserByEmail($email);
    }

    /**
     * @inheritdoc
     */
    public function refreshUser(SymfonyUser $user)
    {
        if (!$this->supportsClass($class = get_class($user))) {
            throw new UnsupportedUserException(
                sprintf('Instances of "%s" are not supported.', $class)
            );
        }

        /** @var UserInterface $user */
        $email = $user->getEmail();

        return $this->findUserByEmail($email);
    }

    /**
     * @inheritdoc
     */
    public function supportsClass($class)
    {
        return is_subclass_of($class, UserInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function hasUser()
    {
        $this->initialize();

        return null !== $this->user;
    }

    /**
     * @inheritdoc
     */
    public function getUser()
    {
        $this->initialize();

        return $this->user;
    }

    /**
     * @inheritdoc
     */
    public function reset()
    {
        $this->user = null;
        $this->initialized = false;
    }

    /**
     * @inheritdoc
     */
    public function findUserByEmail(string $email, bool $throwException = true)
    {
        /** @var UserInterface $user */
        if (null !== $user = $this->userRepository->findOneByEmail($email)) {
            return $user;
        }

        if ($throwException) {
            throw new UsernameNotFoundException(
                sprintf('No user registered for email "%s".', $email)
            );
        }

        return null;
    }

    /**
     * Loads the user once.
     */
    private function initialize()
    {
        if ($this->initialized) {
            return;
        }

        $this->initialized = true;

        if (null === $token = $this->tokenStorage->getToken()) {
            return;
        }

        $user = $token->getUser();
        if (!is_object($user) || !$user instanceof UserInterface) {
            return;
        }

        $this->user = $user;
    }
}
