<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\ToggleAction;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Component\Resource\Config\Registry\ActionRegistryInterface;
use Ekyna\Component\Resource\Exception\LogicException;
use Ekyna\Component\Resource\Exception\UnexpectedTypeException;
use Ekyna\Component\Resource\Model\ResourceInterface;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Extension\Core\Type\Column\BooleanType;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function get_class;
use function sprintf;

/**
 * Class BooleanTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class BooleanTypeExtension extends AbstractColumnTypeExtension
{
    private ActionRegistryInterface $actionRegistry;
    private AuthorizationCheckerInterface $authorization;
    private ResourceHelper $resourceHelper;


    /**
     * Constructor.
     *
     * @param ActionRegistryInterface       $actionRegistry
     * @param AuthorizationCheckerInterface $authorization
     * @param ResourceHelper                $resourceHelper
     */
    public function __construct(
        ActionRegistryInterface $actionRegistry,
        AuthorizationCheckerInterface $authorization,
        ResourceHelper $resourceHelper
    ) {
        $this->actionRegistry = $actionRegistry;
        $this->authorization  = $authorization;
        $this->resourceHelper = $resourceHelper;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        if (null !== $disablePath = $options['disable_property_path']) {
            if ($row->getData($disablePath)) {
                return;
            }
        }

        if (empty($action = $options['action'])) {
            return;
        }

        $resource = $row->getData(null);

        if (!$resource instanceof ResourceInterface) {
            throw new UnexpectedTypeException($resource, ResourceInterface::class);
        }

        if (!$this->resourceHelper->getResourceConfig($resource)->hasAction($action)) {
            if ($action === ToggleAction::class) {
                return;
            }

            throw new LogicException(sprintf('Resource %s has no action %s.', get_class($resource), $action));
        }

        $aCfg = $this->actionRegistry->find($action);

        if (!$this->authorization->isGranted($aCfg->getPermission(), $resource)) {
            return;
        }

        $parameters = $options['parameters'];
        if ($action === ToggleAction::class && !isset($parameters['property'])) {
            $parameters['property'] = $options['property'] ?? $column->getConfig()->getPropertyPath();
        }

        $view->vars['path'] = $this
            ->resourceHelper
            ->generateResourcePath($resource, $aCfg->getName(), $parameters);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'action' => ToggleAction::class,
                'property'  => null,
            ])
            ->setAllowedTypes('action', ['string', 'null'])
            ->setAllowedTypes('property', ['string', 'null']);
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): array
    {
        return [BooleanType::class];
    }
}
