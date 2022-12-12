<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action;

use Ekyna\Component\Resource\Action\AbstractActionBuilder;
use Ekyna\Component\Resource\Action\ActionBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class CrudActions
 * @package Ekyna\Bundle\AdminBundle\Action
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CrudActions extends AbstractActionBuilder implements ActionBuilderInterface
{
    protected const NAME = 'admin_crud';

    public static function configureOptions(OptionsResolver $resolver): void
    {
        $normalizer = function (Options $options, $value) {
            if (false === $value) {
                return false;
            }

            if (is_string($value)) {
                $value = ['template' => $value];
            } elseif (!is_array($value)) {
                $value = [];
            }

            return $value;
        };

        $resolver
            ->setDefined([
                'templates', // The template folder
                'list',      // admin_list options
                'create',    // admin_create options
                'read',      // admin_read options
                'update',    // admin_update options
                'delete',    // admin_delete options
                'form',      // form template for admin_create, admin_update and admin_delete
                'expose',    // Whether to expose all routes (JS)
            ])
            ->setDefaults([
                'summary' => false,
            ])
            ->setAllowedTypes('templates', ['string', 'null'])
            ->setAllowedTypes('list', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('create', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('read', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('update', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('delete', ['array', 'string', 'bool', 'null'])
            ->setAllowedTypes('summary', ['array', 'string', 'bool'])
            ->setAllowedTypes('expose', ['array', 'string', 'bool'])
            ->setAllowedTypes('form', ['string', 'null'])
            ->setAllowedValues('templates', function ($value) {
                return is_null($value) || preg_match('~^@?[A-Za-z0-9]+(/[A-Za-z0-9]+)*+$~', $value);
            })
            ->setNormalizer('list', $normalizer)
            ->setNormalizer('create', $normalizer)
            ->setNormalizer('read', $normalizer)
            ->setNormalizer('update', $normalizer)
            ->setNormalizer('delete', $normalizer)
            ->setNormalizer('summary', $normalizer);
    }

    protected static function getMap(array $config): array
    {
        $actions = [
            'list'    => ListAction::class,
            'create'  => CreateAction::class,
            'read'    => ReadAction::class,
            'update'  => UpdateAction::class,
            'delete'  => DeleteAction::class,
            'summary' => SummaryAction::class,
        ];

        if (isset($config['parent'])) {
            unset($actions['list']);
        }

        return $actions;
    }

    protected static function buildActionOptions(array $all, string $name): array
    {
        $options = $all[$name] ?? [];

        if (!isset($options['template']) && isset($all['templates'])) {
            $options['template'] = sprintf('%s/%s.html.twig', $all['templates'], $name);
        }

        if (in_array($name, ['create', 'update'], true) && !isset($options['form_template'])) {
            if (isset($all['form'])) {
                $options['form_template'] = $all['form'];
            } elseif (isset($all['templates'])) {
                $options['form_template'] = sprintf('%s/_form.html.twig', $all['templates']);
            }
        }

        if (isset($all['expose']) && true === $all['expose']) {
            $options['expose'] = true;
        }

        return $options;
    }
}
