<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\EventListener\SecurityEventListener;
use Ekyna\Bundle\AdminBundle\Repository\OAuthTokenRepository;
use Ekyna\Bundle\AdminBundle\Service\Renderer\OauthRenderer;
use Ekyna\Bundle\AdminBundle\Service\Security\ApiProvider;
use Ekyna\Bundle\AdminBundle\Service\Security\ApiTokenGenerator;
use Ekyna\Component\User\Service\LoginFormAuthenticator;
use Ekyna\Component\User\Service\OAuth\OAuthPassportGenerator;
use Ekyna\Component\User\Service\OAuth\RoutingLoader;
use Ekyna\Component\User\Service\SecurityUtil;
use Ekyna\Component\User\Service\UserProvider;
use Symfony\Component\Security\Http\Event\LoginSuccessEvent;
use Symfony\Component\Security\Http\Event\LogoutEvent;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Security util
        ->set('ekyna_admin.security_util', SecurityUtil::class)

        // OAuth passport generator
        ->set('ekyna_admin.security.oauth_passport_generator', OAuthPassportGenerator::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('ekyna_admin.manager.user'),
                service('ekyna_admin.factory.user'),
                service('ekyna_admin.repository.oauth_token'),
                service('doctrine.orm.default_entity_manager'),
            ])

        // Login renderer
        ->set('ekyna_admin.renderer.oauth', OauthRenderer::class)
            ->args([
                service('knpu.oauth2.registry'),
            ])
            ->tag('twig.runtime')

        // User provider
        ->set('ekyna_admin.provider.user', UserProvider::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('security.token_storage'),
                param('ekyna_admin.class.user'),
            ])

        // Api provider
        ->set('ekyna_admin.security.api_provider', ApiProvider::class)
            ->args([
                service('ekyna_admin.repository.user'),
            ])

        // Api token generator
        ->set('ekyna_admin.security.api_token_generator', ApiTokenGenerator::class)
            ->args([
                service('ekyna_admin.security_util'),
            ])

        // Form login authenticator
        ->set('ekyna_admin.security.authenticator.form_login', LoginFormAuthenticator::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('router'),
                'admin_security_login',
                'admin_dashboard',
            ])

        // OAuth Token repository
        ->set('ekyna_admin.repository.oauth_token', OAuthTokenRepository::class)
            ->args([
                service('doctrine'),
            ])
            ->tag('doctrine.repository_service')

        // OAuth routing loader
        ->set('ekyna_admin.routing.oauth_loader', RoutingLoader::class)
            ->args([
                service('knpu.oauth2.registry'),
                'ekyna_admin',
                param('ekyna_admin.routing_prefix'),
            ])
            ->tag('routing.loader')

        // Security event subscriber
        ->set('ekyna_admin.listener.security', SecurityEventListener::class)
            ->args([
                service('ekyna_admin.provider.user'),
                service('security.authorization_checker'),
                service('ekyna_admin.mailer'),
                abstract_arg('Security notification config'),
            ])
            ->tag('kernel.event_listener', [
                'dispatcher' => 'security.event_dispatcher.admin',
                'event'      => LoginSuccessEvent::class,
                'method'     => 'onLoginSuccess',
                'priority'   => 1024,
            ])
            ->tag('kernel.event_listener', [
                'dispatcher' => 'security.event_dispatcher.admin',
                'event'      => LogoutEvent::class,
                'method'     => 'onLogout',
                'priority'   => 1024,
            ])
    ;
};
