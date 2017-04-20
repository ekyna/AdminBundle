<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Extension;

use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Table\Extension\AbstractTableTypeExtension;
use Ekyna\Component\Table\Extension\Core\Type\TableType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class SummaryTableTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Extension
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class SummaryTableTypeExtension extends AbstractTableTypeExtension
{
    private ResourceHelper $helper;


    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    public function buildRowView(View\RowView $view, RowInterface $row, array $options): void
    {
        if (!$options['resource_summary']) {
            return;
        }

        $resource = $row->getData(null);

        if (!$resource instanceof ResourceInterface) {
            throw new UnexpectedTypeException($resource, ResourceInterface::class);
        }

        $view->vars['attr']['data-summary'] = $this->helper->generateResourcePath($resource, SummaryAction::class);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('resource_summary', false)
            ->setAllowedTypes('resource_summary', 'bool');
    }

    public static function getExtendedTypes(): array
    {
        return [TableType::class];
    }
}
