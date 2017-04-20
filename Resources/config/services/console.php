<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Command\ChangeUserPasswordCommand;
use Ekyna\Bundle\AdminBundle\Command\CreateUserCommand;
use Ekyna\Bundle\AdminBundle\Command\GenerateUserApiTokenCommand;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Create user command
        ->set('ekyna_admin.command.create_user', CreateUserCommand::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('ekyna_admin.manager.user'),
                service('ekyna_admin.security_util'),
                service('ekyna_admin.factory.user'),
                service('ekyna_admin.repository.group'),
            ])
            ->tag('console.command')

        // Change user password command
        ->set('ekyna_admin.command.change_user_password', ChangeUserPasswordCommand::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('ekyna_admin.manager.user'),
                service('ekyna_admin.security_util'),
            ])
            ->tag('console.command')

        // Change user password command
        ->set('ekyna_admin.command.generate_user_api_token', GenerateUserApiTokenCommand::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('ekyna_admin.manager.user'),
                service('ekyna_admin.security_util'),
            ])
            ->tag('console.command')
    ;
};
