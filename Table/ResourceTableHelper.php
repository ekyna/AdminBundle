<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table;

use Ekyna\Bundle\ResourceBundle\Exception\RedirectException;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Table\TableFactoryInterface;
use Ekyna\Component\Table\View\TableView;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class ResourceTableHelper
 * @package Ekyna\Bundle\AdminBundle\Table
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ResourceTableHelper
{
    private TableFactoryInterface     $tableFactory;
    private ResourceRegistryInterface $resourceRegistry;
    private RequestStack              $requestStack;


    public function __construct(
        TableFactoryInterface     $tableFactory,
        ResourceRegistryInterface $resourceRegistry,
        RequestStack              $requestStack
    ) {
        $this->tableFactory = $tableFactory;
        $this->resourceRegistry = $resourceRegistry;
        $this->requestStack = $requestStack;
    }

    public function createResourceTableView(string $resource, array $options = []): TableView
    {
        $options = array_replace(
            [
                'sortable'   => false,
                'filterable' => false,
            ],
            $options,
            // Disable features that would require to return a response
            [
                'batchable'      => false,
                'exportable'     => false,
                'configurable'   => false,
                'profileable'    => false,
                'selection_mode' => null,
            ]
        );

        $config = $this->resourceRegistry->find($resource);

        $table = $this->tableFactory->createTable(
            $config->getUnderscoreId(),
            $config->getData('table'),
            $options
        );

        if ($response = $table->handleRequest($this->requestStack->getMainRequest())) {
            if ($response instanceof RedirectResponse) {
                throw new RedirectException($response->getTargetUrl());
            }
            // TODO Find a way to 'return' the response
        }

        return $table->createView();
    }
}
