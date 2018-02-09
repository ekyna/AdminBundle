<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;

/**
 * Class CollectionType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CollectionType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'collection';
    }
}