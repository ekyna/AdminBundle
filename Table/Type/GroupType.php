<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

/**
 * Class GroupType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupType extends ResourceTableType
{
    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $builder
            ->addDefaultSort('position', ColumnSort::ASC)
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.name',
                'sortable'             => false,
                'route_name'           => 'ekyna_admin_group_admin_show',
                'route_parameters_map' => [
                    'groupId' => 'id',
                ],
                'position'             => 10,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    /*[
                        'label'                => 'ekyna_admin.group.button.edit_permissions',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_admin_group_admin_edit_permissions',
                        'route_parameters_map' => ['groupId' => 'id'],
                        'permission'           => 'edit',
                    ],*/
                    [
                        'label'                => 'ekyna_core.button.move_up',
                        'icon'                 => 'arrow-up',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_admin_group_admin_move_up',
                        'route_parameters_map' => ['groupId' => 'id'],
                        'permission'           => 'edit',
                        // TODO Deny moving group up to super admin
                    ],
                    [
                        'label'                => 'ekyna_core.button.move_down',
                        'icon'                 => 'arrow-down',
                        'class'                => 'primary',
                        'route_name'           => 'ekyna_admin_group_admin_move_down',
                        'route_parameters_map' => ['groupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_admin_group_admin_edit',
                        'route_parameters_map' => ['groupId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_admin_group_admin_remove',
                        'route_parameters_map' => ['groupId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.name',
                'position' => 10,
            ]);
    }
}
