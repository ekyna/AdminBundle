<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Acl\AclOperatorInterface;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class EntityTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityTypeExtension extends AbstractColumnTypeExtension
{
    /**
     * @var AclOperatorInterface
     */
    private $aclOperator;


    /**
     * Constructor.
     *
     * @param AclOperatorInterface $aclOperator
     */
    public function __construct(AclOperatorInterface $aclOperator)
    {
        $this->aclOperator = $aclOperator;
    }

    /**
     * @inheritDoc
     */
    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options)
    {
        $view->vars['block_prefix'] = 'entity';

        if (empty($route = $options['route_name'])) {
            return;
        }

        $accessor = $row->getPropertyAccessor();
        $viewChoices = $view->vars['value'];

        foreach ($viewChoices as &$viewChoice) {
            $value = $viewChoice['value'];
            if (!$this->aclOperator->isAccessGranted($value, 'VIEW')) {
                continue;
            }

            $parameters = $options['route_parameters'];
            if (!empty($options['route_parameters_map'])) {
                foreach ($options['route_parameters_map'] as $parameter => $propertyPath) {
                    if (null !== $value = $accessor->getValue($value, $propertyPath)) {
                        $parameters[$parameter] = $value;
                    }
                }

                if (0 < count(array_diff_key($options['route_parameters_map'], $parameters))) {
                    continue;
                }
            }

            $viewChoice['parameters'] = $parameters;
        }

        $view->vars['value'] = $viewChoices;
        $view->vars['route'] = $route;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'route_name'           => null,
                'route_parameters'     => [],
                'route_parameters_map' => [],
            ])
            ->setAllowedTypes('route_name', ['null', 'string'])
            ->setAllowedTypes('route_parameters', 'array')
            ->setAllowedTypes('route_parameters_map', 'array');
    }

    /**
     * @inheritDoc
     */
    public function getExtendedType()
    {
        return EntityType::class;
    }
}
