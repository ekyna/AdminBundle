<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Reference;

/**
 * Class ApiPass
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ApiPass implements CompilerPassInterface
{
    public function process(ContainerBuilder $container): void
    {
        /** @see \Ekyna\Bundle\ApiBundle\Services\Security\TokenAuthenticator::addValidator */
        $container
            ->getDefinition('ekyna_api.security.authenticator.token')
            ->addMethodCall('setProvider', [new Reference('ekyna_admin.security.api_provider')]);
    }
}
