<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Action\Acl\AbstractAction;
use Ekyna\Bundle\AdminBundle\Action\Acl\NamespaceAction;
use Ekyna\Bundle\AdminBundle\Action\Acl\PermissionAction;
use Ekyna\Bundle\AdminBundle\Action\Acl\ResourceAction;
use Ekyna\Bundle\AdminBundle\Action\User\GenerateApiTokenAction;
use Ekyna\Bundle\AdminBundle\Action\User\GeneratePasswordAction;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // ACL abstract action
        ->set('ekyna_admin.action.acl.abstract', AbstractAction::class)
            ->abstract()
            ->args([
                service('ekyna_resource.acl.manager'),
                service('security.authorization_checker'),
            ])

        // ACL namespace action
        ->set('ekyna_admin.action.acl.namespace', NamespaceAction::class)
            ->parent('ekyna_admin.action.acl.abstract')
            ->tag('ekyna_resource.action')

        // ACL permission action
        ->set('ekyna_admin.action.acl.permission', PermissionAction::class)
            ->parent('ekyna_admin.action.acl.abstract')
            ->tag('ekyna_resource.action')

        // ACL resource action
        ->set('ekyna_admin.action.acl.resource', ResourceAction::class)
            ->parent('ekyna_admin.action.acl.abstract')
            ->tag('ekyna_resource.action')

        // User generate token action
        ->set('ekyna_admin.action.user.generate_api_token', GenerateApiTokenAction::class)
            ->args([
                service('ekyna_admin.security_util'),
            ])
            ->tag('ekyna_resource.action')

        // User generate password action
        ->set('ekyna_admin.action.user.generate_password', GeneratePasswordAction::class)
            ->args([
                service('ekyna_admin.security_util'),
                service('ekyna_admin.mailer'),
            ])
            ->tag('ekyna_resource.action')
    ;
};
