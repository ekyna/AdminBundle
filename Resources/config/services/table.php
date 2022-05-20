<?php

declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Ekyna\Bundle\AdminBundle\Table\Context\Profile\UserStorage;
use Ekyna\Bundle\AdminBundle\Table\Context\TableTypeExtension;
use Ekyna\Bundle\AdminBundle\Table\Extension\SummaryTableTypeExtension;
use Ekyna\Bundle\AdminBundle\Table\ResourceTableHelper;
use Ekyna\Bundle\AdminBundle\Table\Type\Column;
use Ekyna\Bundle\AdminBundle\Table\Type\Filter;
use Ekyna\Bundle\AdminBundle\Table\Type\UserType;

return static function (ContainerConfigurator $container) {
    $container
        ->services()

        // Resource table helper
        ->set('ekyna_admin.helper.resource_table', ResourceTableHelper::class)
            ->args([
                service('table.factory'),
                service('ekyna_resource.registry.resource'),
                service('request_stack'),
            ])
            ->tag('twig.runtime')

        // Table profile storage
        ->set('ekyna_admin.table.profile.user_storage', UserStorage::class)
            ->args([
                service('ekyna_admin.provider.user'),
                service('doctrine.orm.default_entity_manager'),
            ])

        // Resource table type extension
        ->set('ekyna_admin.table_type_extension.summary', SummaryTableTypeExtension::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('table.type_extension')

        // Table type extension
        ->set('ekyna_admin.table_type_extension.admin', TableTypeExtension::class)
            ->args([
                service('ekyna_admin.table.profile.user_storage'),
            ])
            ->tag('table.type_extension')

        // User table type
        ->set('ekyna_admin.table_type.user', UserType::class)
            ->args([
                service('ekyna_admin.provider.user'),
            ])
            ->tag('table.type')

        // Actions column type extension
        ->set('ekyna_admin.table_column_type_extension.actions', Column\ActionsTypeExtension::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('table.column_type_extension')

        // Anchor column type extension
        ->set('ekyna_admin.table_column_type_extension.anchor', Column\AnchorTypeExtension::class)
            ->args([
                service('ekyna_resource.helper'),
                service('security.authorization_checker'),
            ])
            ->tag('table.column_type_extension')

        // Boolean column type extension
        ->set('ekyna_admin.column_type_extension.boolean', Column\BooleanTypeExtension::class)
            ->args([
                service('ekyna_resource.registry.action'),
                service('security.authorization_checker'),
                service('ekyna_resource.helper'),
            ])
            ->tag('table.column_type_extension')

        // Entity column type extension
        ->set('ekyna_admin.table_column_type_extension.entity', Column\EntityTypeExtension::class)
            ->args([
                service('security.authorization_checker'),
                service('router'),
            ])
            ->tag('table.column_type_extension')

        // Nested actions column type extension
        ->set('ekyna_admin.table_column_type_extension.nested_actions', Column\NestedActionsTypeExtension::class)
            ->args([
                service('ekyna_resource.helper'),
            ])
            ->tag('table.column_type_extension')

        // Constant column type
        ->set('ekyna_admin.table_column_type.constant_choice', Column\ConstantChoiceType::class)
            ->args([
                service('translator'),
            ])
            ->tag('table.column_type')

        // Resource column type
        ->set('ekyna_admin.table_column_type.resource', Column\ResourceType::class)
            ->args([
                service('ekyna_resource.helper'),
                service('security.authorization_checker'),
                service('translator'),
            ])
            ->tag('table.column_type')

        // Constant filter type
        ->set('ekyna_admin.table_filter_type.constant_choice', Filter\ConstantChoiceType::class)
            ->args([
                service('translator'),
            ])
            ->tag('table.filter_type')
    ;
};
