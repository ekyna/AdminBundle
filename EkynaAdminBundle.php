<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle;

use Ekyna\Bundle\AdminBundle\DependencyInjection\Compiler as Pass;
use Ekyna\Component\User\DependencyInjection\OAuthPass;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Class EkynaAdminBundle
 * @package Ekyna\Bundle\AdminBundle
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminBundle extends Bundle
{
    public function build(ContainerBuilder $container): void
    {
        parent::build($container);

        $container->addCompilerPass(new Pass\ActionAutoConfigurePass());
        $container->addCompilerPass(new Pass\ApiPass());
        $container->addCompilerPass(new Pass\DashboardWidgetRegistryPass());
        $container->addCompilerPass(new Pass\ShowRegistryPass());

        $container->addCompilerPass(new OAuthPass('ekyna_admin', 'ekyna_admin.security.oauth_passport_generator', [
            'target_route' => 'admin_dashboard',
        ]));
    }
}
