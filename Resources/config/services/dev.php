<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Service\Fixtures\AdminProcessor;
use Ekyna\Bundle\AdminBundle\Service\Fixtures\AdminProvider;
use Ekyna\Bundle\AdminBundle\Service\Security\DevAuthenticator;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Dev authenticator
        ->set('ekyna_admin.security.authenticator.dev', DevAuthenticator::class)
            ->args([
                service('ekyna_admin.repository.user'),
            ])

        // Admin provider
        ->set('ekyna_admin.fixtures.admin_provider', AdminProvider::class)
            ->args([
                service('ekyna_admin.repository.group'),
            ])
            ->tag('nelmio_alice.faker.provider')

        // Admin processor
        ->set('ekyna_admin.fixtures.admin_processor', AdminProcessor::class)
            ->args([
                service('security.password_encoder'),
            ])
            ->tag('fidry_alice_data_fixtures.processor')
    ;
};
