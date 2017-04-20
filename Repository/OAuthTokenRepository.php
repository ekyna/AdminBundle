<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepositoryInterface;
use Doctrine\Persistence\ManagerRegistry;
use Ekyna\Bundle\AdminBundle\Entity\OAuthToken;
use Ekyna\Component\User\Repository\AbstractOAuthTokenRepository;

/**
 * Class OAuthTokenRepository
 * @package Ekyna\Bundle\AdminBundle\Repository
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class OAuthTokenRepository extends AbstractOAuthTokenRepository implements ServiceEntityRepositoryInterface
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, OAuthToken::class);
    }
}
