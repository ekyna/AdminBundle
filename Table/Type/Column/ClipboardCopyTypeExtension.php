<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Extension\Core\Type\Column\TextType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ClipboardCopyTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class ClipboardCopyTypeExtension extends AbstractColumnTypeExtension
{
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (!$options['clipboard_copy']) {
            return;
        }

        $view->vars['attr']['data-clipboard-copy'] = (string)$view->vars['value'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('clipboard_copy', false)
            ->setAllowedTypes('clipboard_copy', 'bool');
    }

    public static function getExtendedTypes(): array
    {
        return [TextType::class];
    }
}
