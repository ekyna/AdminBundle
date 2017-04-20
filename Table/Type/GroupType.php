<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Bundle\AdminBundle\Action;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\Table\Util\ColumnSort;

use function Symfony\Component\Translation\t;

/**
 * Class GroupType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class GroupType extends AbstractResourceType
{
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder
            ->addDefaultSort('position', ColumnSort::ASC)
            ->addColumn('name', BType\Column\AnchorType::class, [
                'label'         => t('field.name', [], 'EkynaUi'),
                'property_path' => null,
                'sortable'      => false,
                'position'      => 10,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    Action\MoveUpAction::class,
                    Action\MoveDownAction::class,
                    Action\UpdateAction::class,
                    Action\DeleteAction::class,
                ],
            ])
            ->addFilter('name', CType\Filter\TextType::class, [
                'label'        => t('field.name', [], 'EkynaUi'),
                'position'     => 10,
            ]);
    }
}
