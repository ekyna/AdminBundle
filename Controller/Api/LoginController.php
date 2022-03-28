<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Controller\Api;

use Ekyna\Bundle\AdminBundle\Manager\UserManagerInterface;
use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\AdminBundle\Service\Security\ApiTokenGenerator;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

/**
 * Class LoginController
 * @package Ekyna\Bundle\AdminBundle\Controller
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class LoginController
{
    private UserProviderInterface $provider;
    private ApiTokenGenerator     $generator;
    private UserManagerInterface  $manager;


    public function __construct(
        UserProviderInterface $provider,
        ApiTokenGenerator $generator,
        UserManagerInterface $manager
    ) {
        $this->provider = $provider;
        $this->generator = $generator;
        $this->manager = $manager;
    }

    public function __invoke(): Response
    {
        $user = $this->provider->getUser();

        if (!$user instanceof UserInterface) {
            return new Response('', Response::HTTP_UNAUTHORIZED);
        }

        if ($this->generator->generate($user)) {
            $this->manager->persist($user);
            $this->manager->flush();
        }

        return new JsonResponse([
            'token'      => $user->getApiToken(),
            'expires_at' => $user->getApiExpiresAt()->getTimestamp(),
        ]);
    }
}
