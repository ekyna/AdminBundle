<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Action\AdminActionInterface;
use Ekyna\Bundle\AdminBundle\EventListener\GroupEventSubscriber;
use Ekyna\Bundle\AdminBundle\EventListener\UserEventSubscriber;
use Ekyna\Bundle\AdminBundle\Install\AdminInstaller;
use Ekyna\Bundle\AdminBundle\Service\Mailer\AdminMailer;
use Ekyna\Bundle\AdminBundle\Service\Mailer\MailerFactory;
use Ekyna\Bundle\AdminBundle\Service\Pin\PinHelper;
use Ekyna\Bundle\AdminBundle\Service\Search\SearchHelper;
use Ekyna\Bundle\AdminBundle\Service\Search\UserRepository;
use Ekyna\Bundle\AdminBundle\Service\Setting\GeneralSettingSchema;
use Ekyna\Bundle\AdminBundle\Service\Setting\NotificationSettingSchema;
use Ekyna\Bundle\SettingBundle\DependencyInjection\Compiler\RegisterSchemasPass;
use Ekyna\Component\User\Service\UserProvider;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // User provider
        ->set('ekyna_admin.provider.user', UserProvider::class)
            ->args([
                service('security.token_storage'),
                param('ekyna_admin.class.user'),
            ])

        // Admin mailer
        ->set('ekyna_admin.mailer', AdminMailer::class)
            ->args([
                service('ekyna_setting.manager'),
                service('translator'),
                service('twig'),
                service('router'),
                service('mailer'),
            ])

        // Mailer factory
        ->set('ekyna_admin.factory.mailer', MailerFactory::class)
            ->args([
                service('mailer'),
                service('mailer.transport_factory'),
                service('ekyna_admin.provider.user'),
                service('ekyna_admin.repository.user'),
                service('messenger.default_bus')->ignoreOnInvalid(),
                service('event_dispatcher')->ignoreOnInvalid(),
            ])

        // Search helper
        ->set('ekyna_admin.helper.search', SearchHelper::class)
            ->args([
                service('ekyna_resource.search'),
                service('request_stack'),
                service('router'),
                service('twig'),
            ])
            ->tag('twig.runtime')

        // Pin helper
        ->set('ekyna_admin.helper.pin', PinHelper::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('ekyna_admin.provider.user'),
                service('ekyna_resource.helper'),
                service('doctrine.orm.default_entity_manager'),
            ])
            ->tag('twig.runtime')

        // User event subscriber
        ->set('ekyna_admin.listener.user', UserEventSubscriber::class)
            ->call('setPasswordHasher', [service('security.password_encoder')]) // TODO (Sf 6) security.user_password_hasher
            ->call('setSecurityUtil', [service('ekyna_admin.security_util')])
            ->tag('resource.event_subscriber')

        // Group event subscriber
        ->set('ekyna_admin.listener.group', GroupEventSubscriber::class)
            ->args([
                service('security.token_storage'),
                service('security.authorization_checker'),
            ])
            ->tag('resource.event_subscriber')

        // User search repository
        ->set('ekyna_admin.search.user', UserRepository::class)
            ->call('setGroupRepository', [
                service('ekyna_admin.repository.group')
            ])

        // Setting schemas
        ->set('ekyna_admin.setting.general', GeneralSettingSchema::class)
            ->tag(RegisterSchemasPass::TAG, ['namespace' => 'general', 'position' => 0])
        ->set('ekyna_admin.setting.notification', NotificationSettingSchema::class)
            ->tag(RegisterSchemasPass::TAG, ['namespace' => 'notification', 'position' => 1])

        // Routing loader
        ->set('ekyna_admin.routing.resource')
            ->parent('ekyna_resource.routing.resource_loader')
            ->args([
                'admin_resource',
                AdminActionInterface::class,
                param('ekyna_admin.routing_prefix'),
                param('kernel.default_locale'),
                param('kernel.environment'),
            ])
            ->tag('routing.loader')

        // Installer
        ->set('ekyna_admin.installer', AdminInstaller::class)
            ->args([
                service('ekyna_admin.repository.group'),
                service('ekyna_admin.manager.group'),
                service('ekyna_admin.factory.group'),
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.acl.manager'),
            ])
            ->tag('ekyna_install.installer', ['priority' => 100])
    ;
};
