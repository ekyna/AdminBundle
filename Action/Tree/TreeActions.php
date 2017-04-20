<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Tree;

use Ekyna\Component\Resource\Action\AbstractActionBuilder;
use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class TreeActions
 * @package Ekyna\Bundle\AdminBundle\Action\Tree
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TreeActions extends AbstractActionBuilder implements ActionBuilderInterface
{
    protected const NAME = 'admin_tree';

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $normalizer = function (Options $options, $value) {
            if (false === $value) {
                return false;
            }

            if (is_array($value)) {
                return $value;
            }

            return [];
        };

        $resolver
            ->setDefined([
                'move_up',      // admin_tree_move_up options
                'move_down',    // admin_tree_move_down options
                'create_child', // admin_tree_create_child options
            ])
            ->setAllowedTypes('move_up', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('move_down', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('create_child', ['array', 'string', 'bool', 'null'])
            ->setNormalizer('move_up', $normalizer)
            ->setNormalizer('move_down', $normalizer)
            ->setNormalizer('create_child', $normalizer);
    }

    protected static function getMap(array $config): array
    {
        return [
            'move_up'      => MoveUpAction::class,
            'move_down'    => MoveDownAction::class,
            'create_child' => CreateChildAction::class,
        ];
    }
}
