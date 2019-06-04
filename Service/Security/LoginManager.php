<?php

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Http\Session\SessionAuthenticationStrategyInterface;

/**
 * Class LoginManager
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class LoginManager
{
    /**
     * @var TokenStorageInterface
     */
    private $tokenStorage;

    /**
     * @var UserCheckerInterface
     */
    private $userChecker;

    /**
     * @var SessionAuthenticationStrategyInterface
     */
    private $sessionStrategy;

    /**
     * @var RequestStack
     */
    private $requestStack;


    /**
     * LoginManager constructor.
     *
     * @param TokenStorageInterface                  $tokenStorage
     * @param UserCheckerInterface                   $userChecker
     * @param SessionAuthenticationStrategyInterface $sessionStrategy
     * @param RequestStack                           $requestStack
     */
    public function __construct(
        UserCheckerInterface $userChecker,
        TokenStorageInterface $tokenStorage,
        RequestStack $requestStack,
        SessionAuthenticationStrategyInterface $sessionStrategy
    ) {
        $this->tokenStorage = $tokenStorage;
        $this->userChecker = $userChecker;
        $this->sessionStrategy = $sessionStrategy;
        $this->requestStack = $requestStack;
    }

    /**
     * Login the given user.
     *
     * @param string        $firewallName
     * @param UserInterface $user
     *
     * @return TokenInterface
     */
    final public function logInUser(string $firewallName, UserInterface $user): TokenInterface
    {
        $this->userChecker->checkPreAuth($user);

        $token = $this->createToken($firewallName, $user);
        $request = $this->requestStack->getCurrentRequest();

        if (null !== $request) {
            $this->sessionStrategy->onAuthentication($request, $token);
        }

        $this->tokenStorage->setToken($token);

        return $token;
    }

    /**
     * Creates the security token.
     *
     * @param string        $firewall
     * @param UserInterface $user
     *
     * @return UsernamePasswordToken
     */
    private function createToken(string $firewall, UserInterface $user): TokenInterface
    {
        return new UsernamePasswordToken($user, null, $firewall, $user->getRoles());
    }
}
