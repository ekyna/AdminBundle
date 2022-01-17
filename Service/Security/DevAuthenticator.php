<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Http\Authenticator\AbstractAuthenticator;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;
use Symfony\Component\Security\Http\Authenticator\Passport\PassportInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\SelfValidatingPassport;
use Symfony\Component\Security\Http\EntryPoint\AuthenticationEntryPointInterface;

/**
 * Class DevAuthenticator
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class DevAuthenticator extends AbstractAuthenticator implements AuthenticationEntryPointInterface
{
    public const HEADER = 'X-Ekyna-Dev-Admin';

    private UserRepositoryInterface $userRepository;

    public function __construct(UserRepositoryInterface $userRepository)
    {
        $this->userRepository = $userRepository;
    }

    public function start(Request $request, AuthenticationException $authException = null): Response
    {
        return new Response('Auth header required', 401);
    }

    public function supports(Request $request): ?bool
    {
        return $request->headers->has(self::HEADER);
    }

    public function authenticate(Request $request): PassportInterface
    {
        $email = $request->headers->get(self::HEADER);
        if (null === $email) {
            throw new CustomUserMessageAuthenticationException();
        }

        $loader = function ($email) {
            return $this->userRepository->findOneByEmail($email);
        };

        return new SelfValidatingPassport(new UserBadge($email, $loader));
    }

    public function onAuthenticationSuccess(Request $request, TokenInterface $token, string $firewallName): ?Response
    {
        return null;
    }

    public function onAuthenticationFailure(Request $request, AuthenticationException $exception): ?Response
    {
        $data = [
            'message' => strtr($exception->getMessageKey(), $exception->getMessageData())
        ];

        return new JsonResponse($data, Response::HTTP_UNAUTHORIZED);
    }
}
