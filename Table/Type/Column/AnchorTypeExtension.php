<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\AnchorType;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

/**
 * Class AnchorTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AnchorTypeExtension extends AbstractColumnTypeExtension
{
    private ActionRegistryInterface       $actionRegistry;
    private AuthorizationCheckerInterface $authorization;
    private ResourceHelper                $resourceHelper;

    public function __construct(
        ActionRegistryInterface       $actionRegistry,
        AuthorizationCheckerInterface $authorization,
        ResourceHelper                $resourceHelper
    ) {
        $this->actionRegistry = $actionRegistry;
        $this->authorization = $authorization;
        $this->resourceHelper = $resourceHelper;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (empty($action = $options['action'])) {
            return;
        }

        $resource = $row->getData($options['resource_path']);

        if (!$resource instanceof ResourceInterface) {
            throw new UnexpectedTypeException($resource, ResourceInterface::class);
        }

        if (!$this->resourceHelper->getResourceConfig($resource)->hasAction($action)) {
            return;
        }

        $aCfg = $this->actionRegistry->find($action);

        if (!$this->authorization->isGranted($aCfg->getPermission(), $resource)) {
            return;
        }

        $view->vars['path'] = $this
            ->resourceHelper
            ->generateResourcePath($resource, $aCfg->getName(), $options['parameters']);
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefault('action', ReadAction::class)
            ->setDefault('resource_path', null)
            ->setAllowedTypes('action', ['string', 'null'])
            ->setAllowedTypes('resource_path', ['string', 'null']);
    }

    public static function getExtendedTypes(): array
    {
        return [AnchorType::class];
    }
}
