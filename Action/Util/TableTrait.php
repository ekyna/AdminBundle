<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Action\Util;

use Ekyna\Component\Table\TableFactoryInterface;
use Ekyna\Component\Table\TableInterface;

/**
 * Trait TableTrait
 * @package Ekyna\Bundle\AdminBundle\Action\Util
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
trait TableTrait
{
    private TableFactoryInterface $tableFactory;


    /**
     * Sets the table factory.
     *
     * @param TableFactoryInterface $tableFactory
     *
     * @required
     */
    public function setTableFactory(TableFactoryInterface $tableFactory): void
    {
        $this->tableFactory = $tableFactory;
    }

    /**
     * Creates a table.
     *
     * @param string $name
     * @param string $type
     * @param array  $options
     *
     * @return TableInterface
     */
    protected function createTable(string $name, string $type, array $options = []): TableInterface
    {
        return $this
            ->tableFactory
            ->createTable($name, $type, $options);
    }
}
