<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Bundle\AdminBundle\Action\Tree;
use Ekyna\Bundle\ResourceBundle\Helper\ResourceHelper;
use Ekyna\Bundle\ResourceBundle\Service\Routing\RoutingUtil;
use Ekyna\Bundle\TableBundle\Extension\Type\Column\NestedActionsType;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class NestedActionsTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class NestedActionsTypeExtension extends AbstractColumnTypeExtension
{
    private ResourceHelper $helper;


    /**
     * Constructor.
     *
     * @param ResourceHelper $helper
     */
    public function __construct(ResourceHelper $helper)
    {
        $this->helper = $helper;
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        $actionToRouteNormalizer = function (string $name) {
            return function (Options $options, $value) use ($name) {
                if (!empty($value)) {
                    return $value;
                }

                if (!$resource = $options['resource']) {
                    return $value;
                }

                if (!$action = $options[$name]) {
                    return $value;
                }

                return $this->helper->getRoute($resource, $action);
            };
        };

        $resolver
            ->setDefaults([
                'move_up_action'   => Tree\MoveUpAction::class,
                'move_down_action' => Tree\MoveDownAction::class,
                'new_child_action' => Tree\CreateChildAction::class,
            ])
            ->setAllowedTypes('move_up_action', ['string', 'null'])
            ->setAllowedTypes('move_down_action', ['string', 'null'])
            ->setAllowedTypes('new_child_action', ['string', 'null'])
            ->setNormalizer('parameters_map', function (Options $options, $value) {
                if (!empty($value)) {
                    return $value;
                }

                if (!$resource = $options['resource']) {
                    return $value;
                }

                $rCfg = $this->helper->getResourceConfig($resource);

                return [RoutingUtil::getRouteParameter($rCfg) => 'id']; // TODO What about parent ?
            })
            ->setNormalizer('move_up_route', $actionToRouteNormalizer('move_up_action'))
            ->setNormalizer('move_down_route', $actionToRouteNormalizer('move_down_action'))
            ->setNormalizer('new_child_route', $actionToRouteNormalizer('new_child_action'));
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): array
    {
        return [NestedActionsType::class];
    }
}
