<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Service\Acl\AclRenderer;
use Ekyna\Bundle\AdminBundle\Service\Renderer\AdminRenderer;
use Ekyna\Bundle\AdminBundle\Twig\AclExtension;
use Ekyna\Bundle\AdminBundle\Twig\AdminExtension;
use Ekyna\Bundle\AdminBundle\Twig\ShowExtension;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Acl renderer
        ->set('ekyna_admin.renderer.acl', AclRenderer::class)
            ->args([
                service('ekyna_resource.acl.manager'),
                service('twig'),
                service('ekyna_resource.helper'),
            ])
            ->tag('twig.runtime')

        // Admin renderer
        ->set('ekyna_admin.renderer.ui', AdminRenderer::class)
            ->args([
                service('ekyna_resource.helper'),
                service('ekyna_admin.helper.pin'),
                service('security.authorization_checker'),
                service('ekyna_ui.renderer'),
                abstract_arg('The admin ui configuration'), // Replaced by DI extension
            ])
            ->tag('twig.runtime')

        // Twig extensions
        ->set('ekyna_admin.twig.extension.admin', AdminExtension::class)
            ->tag('twig.extension')
        ->set('ekyna_admin.twig.extension.show', ShowExtension::class)
            ->tag('twig.extension')
        ->set('ekyna_admin.twig.extension.acl', AclExtension::class)
            ->tag('twig.extension')
    ;
};
