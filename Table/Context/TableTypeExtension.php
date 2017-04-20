<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Context;

use Ekyna\Component\Table\Extension\AbstractTableTypeExtension;
use Ekyna\Component\Table\Extension\Core\Type\TableType;
use Ekyna\Component\Table\TableBuilderInterface;

/**
 * Class TableTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Context
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TableTypeExtension extends AbstractTableTypeExtension
{
    private Profile\UserStorage $profileStorage;


    /**
     * Constructor.
     *
     * @param Profile\UserStorage $profileStorage
     */
    public function __construct(Profile\UserStorage $profileStorage)
    {
        $this->profileStorage = $profileStorage;
    }

    /**
     * @inheritDoc
     */
    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $builder->setProfileStorage($this->profileStorage);
    }

    /**
     * @inheritDoc
     */
    public static function getExtendedTypes(): array
    {
        return [TableType::class];
    }
}
