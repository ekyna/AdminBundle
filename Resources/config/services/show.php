<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Show\Extension\Core\CoreExtension;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\DependencyInjectionExtension;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type\ChoiceType;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type\ConstantChoiceType;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type\ResourceType;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type\TableType;
use Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type\TranslationsType;
use Ekyna\Bundle\AdminBundle\Show\ShowRegistry;
use Ekyna\Bundle\AdminBundle\Show\ShowRegistryInterface;
use Ekyna\Bundle\AdminBundle\Show\ShowRenderer;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Core extension
        ->set('ekyna_admin.show.core_extension', CoreExtension::class)

        // DI extension
        ->set('ekyna_admin.show.dependency_injection_extension', DependencyInjectionExtension::class)
            ->args([
                abstract_arg('The show types services locator'),
            ])

        // Type registry
        ->set('ekyna_admin.show.type_registry', ShowRegistry::class)
            ->args([
                [
                    service('ekyna_admin.show.core_extension'),
                    service('ekyna_admin.show.dependency_injection_extension'),
                ],
            ])
            ->alias(ShowRegistryInterface::class, 'ekyna_admin.show.type_registry')

        // Translations type
        ->set('ekyna_admin.show.type.translations', TranslationsType::class)
            ->args([
                param('ekyna_resource.locales'),
            ])
            ->tag('ekyna_admin.show.type')

        // Choice type
        ->set('ekyna_admin.show.type.choice', ChoiceType::class)
            ->args([
                service('translator'),
            ])
            ->tag('ekyna_admin.show.type')

        // Constant choice type
        ->set('ekyna_admin.show.type.constant_choice', ConstantChoiceType::class)
            ->args([
                service('translator'),
            ])
            ->tag('ekyna_admin.show.type')

        // Resource type
        ->set('ekyna_admin.show.type.resource', ResourceType::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('ekyna_admin.show.type')

        // Table type
        ->set('ekyna_admin.show.type.table', TableType::class)
            ->args([
                service('ekyna_resource.registry.resource'),
                service('table.factory'),
                service('request_stack'),
            ])
            ->tag('ekyna_admin.show.type')

        // Show renderer
        ->set('ekyna_admin.show.renderer', ShowRenderer::class)
            ->args([
                service('ekyna_admin.show.type_registry'),
                service('twig'),
            ])
            ->tag('twig.runtime')
    ;
};
