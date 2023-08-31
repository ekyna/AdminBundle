<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Action\SummaryAction;
use Ekyna\Bundle\AdminBundle\Model\Ui;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Bundle\TableBundle\Model\Anchor;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function is_string;

/**
 * Class AnchorTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AnchorTypeExtension extends AbstractColumnTypeExtension
{
    public function __construct(
        private readonly ResourceHelper                $resourceHelper,
        private readonly AuthorizationCheckerInterface $authorization
    ) {
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (empty($options['action'])) {
            return;
        }

        $anchor = $view->vars['anchor'] ?? null;
        if (!$anchor instanceof Anchor) {
            return;
        }

        $resource = $row->getData($options['resource_path']);

        if (!$resource instanceof ResourceInterface) {
            throw new UnexpectedTypeException($resource, ResourceInterface::class);
        }

        $this->addAction($anchor, $resource, $options);

        if (false === $summary = $options['summary']) {
            return;
        }

        if (is_string($summary)) {
            $resource = $row->getData($summary);
        }

        if (!$resource instanceof ResourceInterface) {
            throw new UnexpectedTypeException($resource, ResourceInterface::class);
        }

        $this->addSummary($anchor, $resource);
    }

    private function addAction(Anchor $anchor, ResourceInterface $resource, array $options): void
    {
        if (!$this->resourceHelper->getResourceConfig($resource)->hasAction($options['action'])) {
            return;
        }

        $aCfg = $this->resourceHelper->getActionConfig($options['action']);

        if (!$this->authorization->isGranted($aCfg->getPermission(), $resource)) {
            return;
        }

        $anchor->attr['href'] = $this
            ->resourceHelper
            ->generateResourcePath($resource, $aCfg->getName(), $options['parameters']);
    }

    private function addSummary(Anchor $anchor, ResourceInterface $resource): void
    {
        if (!$this->resourceHelper->getResourceConfig($resource)->hasAction(SummaryAction::class)) {
            return;
        }

        $aCfg = $this->resourceHelper->getActionConfig(SummaryAction::class);

        if (!$this->authorization->isGranted($aCfg->getPermission(), $resource)) {
            return;
        }

        $anchor->attr[Ui::SIDE_DETAIL_ATTR] = $this
            ->resourceHelper
            ->generateResourcePath($resource, $aCfg->getName());
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('action', ReadAction::class)
            ->setDefault('resource_path', null)
            ->setDefault('summary', false)
            ->setAllowedTypes('action', ['string', 'null'])
            ->setAllowedTypes('resource_path', ['string', 'null'])
            ->setAllowedTypes('summary', ['bool', 'string']);
    }

    public static function getExtendedTypes(): array
    {
        return [AnchorType::class];
    }
}
