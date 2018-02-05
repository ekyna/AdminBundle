<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;

/**
 * Class TextType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class TextType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'text';
    }
}