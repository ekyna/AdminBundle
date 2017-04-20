<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Util;

use Ekyna\Bundle\AdminBundle\Action\ListAction;
use Ekyna\Bundle\AdminBundle\Action\ReadAction;
use Ekyna\Bundle\AdminBundle\Service\Menu\MenuBuilder;
use Ekyna\Component\Resource\Action\Context;
use Ekyna\Component\Resource\Config\ActionConfig;

use function array_replace;

/**
 * Trait BreadcrumbTrait
 * @package Ekyna\Bundle\AdminBundle\Action\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 *
 * @property Context      $context
 * @property ActionConfig $config
 */
trait BreadcrumbTrait
{
    protected MenuBuilder $menuBuilder;


    /**
     * Sets the menu builder.
     *
     * @required
     */
    public function setMenuBuilder(MenuBuilder $builder): void
    {
        $this->menuBuilder = $builder;
    }

    /**
     * @see MenuBuilder::breadcrumbAppend()
     */
    protected function addBreadcrumbItem(array $item = []): void
    {
        $item = array_replace([
            'resource_config' => $this->context->getConfig(),
            'resource'        => $this->context->getResource(),
        ], $item);

        if (!isset($item['action'])) {
            $item['action_config'] = $this->config;
        }

        $this->menuBuilder->breadcrumbAppend($item);
    }

    protected function breadcrumbFromContext(Context $context): void
    {
        if ($parent = $context->getParent()) {
            $this->breadcrumbFromContext($parent);
        }

        $this->menuBuilder->breadcrumbAppend([
            'resource_config' => $context->getConfig(),
            'action'          => ListAction::class,
            'route'           => $context->getConfig()->hasAction(ListAction::class) ? null : false,
        ]);

        if ($context !== $this->context) {
            $this->menuBuilder->breadcrumbAppend([
                'resource_config' => $context->getConfig(),
                'resource'        => $context->getResource(),
                'action'          => ReadAction::class,
            ]);

            return;
        }

        $resource = $this->context->getResource();
        if ($resource && $resource->getId() && $this->config->getClass() !== ReadAction::class) {
            $this->menuBuilder->breadcrumbAppend([
                'resource_config' => $this->context->getConfig(),
                'resource'        => $this->context->getResource(),
                'action'          => ReadAction::class,
                'route'           => $context->getConfig()->hasAction(ReadAction::class) ? null : false,
            ]);
        }

        $this->addBreadcrumbItem([
            'route' => false,
        ]);
    }
}
