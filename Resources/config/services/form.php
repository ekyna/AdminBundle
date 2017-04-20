<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Form\Extension\AdminTypeExtension;
use Ekyna\Bundle\AdminBundle\Form\Extension\ResourceChoiceTypeExtension;
use Ekyna\Bundle\AdminBundle\Form\Extension\ResourceSearchTypeExtension;
use Ekyna\Bundle\AdminBundle\Form\Type\UserType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Admin form type extension
        ->set('ekyna_admin.form_type_extension.admin', AdminTypeExtension::class)
            ->args([
                service('security.authorization_checker'),
            ])
            ->tag('form.type_extension')

        // Resource choice type extension
        ->set('ekyna_admin.form_type_extension.resource_choice', ResourceChoiceTypeExtension::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('form.type_extension')

        // Resource search type extension
        ->set('ekyna_admin.form_type_extension.resource_search', ResourceSearchTypeExtension::class)
            ->tag('form.type_extension')

        // User form type
        ->set('ekyna_admin.form_type.user', UserType::class)
            ->args([
                service('security.authorization_checker'),
            ])
            ->tag('form.type')
    ;
};
