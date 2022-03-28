<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Controller\Api\LoginController;
use Ekyna\Bundle\AdminBundle\Controller\Api\User\SignatureController;
use Ekyna\Bundle\AdminBundle\Controller\DashboardController;
use Ekyna\Bundle\AdminBundle\Controller\PinController;
use Ekyna\Bundle\AdminBundle\Controller\ToolbarController;
use Ekyna\Component\User\Controller\SecurityController;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Api controller
        ->set('ekyna_admin.controller.api.login', LoginController::class)
            ->args([
                service('ekyna_admin.provider.user'),
                service('ekyna_admin.security.api_token_generator'),
                service('ekyna_admin.manager.user'),
            ])
            ->alias(LoginController::class, 'ekyna_admin.controller.api.login')->public()

        // Api User Signature controller
        ->set('ekyna_admin.controller.api.user.signature', SignatureController::class)
            ->args([
                service('ekyna_admin.repository.user'),
                service('ekyna_admin.renderer.user_signature'),
                param('kernel.debug'),
            ])
            ->alias(SignatureController::class, 'ekyna_admin.controller.api.user.signature')->public()

        // Dashboard controller
        ->set('ekyna_admin.controller.dashboard', DashboardController::class)
            ->args([
                service('ekyna_admin.dashboard'),
                service('twig'),
            ])
            ->alias(DashboardController::class, 'ekyna_admin.controller.dashboard')->public()

        // Toolbar controller
        ->set('ekyna_admin.controller.toolbar', ToolbarController::class)
            ->args([
                service('ekyna_admin.helper.search'),
                service('event_dispatcher'),
            ])
            ->alias(ToolbarController::class, 'ekyna_admin.controller.toolbar')->public()

        // Security controller
        ->set('ekyna_admin.controller.security', SecurityController::class)->public()
            ->args([
                service('security.authentication_utils'),
                service('twig'),
                abstract_arg('Admin security controller configuration'),
            ])

        // Pin controller
        ->set('ekyna_admin.controller.pin', PinController::class)
            ->args([
                service('ekyna_admin.helper.pin'),
                service('ekyna_resource.registry.resource'),
                service('ekyna_resource.repository.factory'),
            ])
            ->alias(PinController::class, 'ekyna_admin.controller.pin')->public()
    ;
};
