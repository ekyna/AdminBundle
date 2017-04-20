<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Service\Security;

use Ekyna\Bundle\AdminBundle\Repository\UserRepositoryInterface;
use Ekyna\Bundle\ApiBundle\Services\Security\ApiProviderInterface;
use Symfony\Component\Security\Http\Authenticator\Passport\Badge\UserBadge;

/**
 * Class ApiProvider
 * @package Ekyna\Bundle\AdminBundle\Service\Security
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiProvider implements ApiProviderInterface
{
    private UserRepositoryInterface $repository;

    public function __construct(UserRepositoryInterface $repository)
    {
        $this->repository = $repository;
    }

    public function provide(string $token): UserBadge
    {
        return new UserBadge($token, function($identifier) {
            return $this->repository->findOneByApiToken($identifier);
        });
    }
}
