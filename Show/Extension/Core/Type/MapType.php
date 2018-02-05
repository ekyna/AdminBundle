<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class MapType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class MapType extends AbstractType
{
    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = [])
    {
        if (!is_array($value)) {
            throw new \InvalidArgumentException("Expected key/value array.");
        }

        parent::build($view, $value, $options);
    }

    /**
     * @inheritDoc
     */
    public function getWidgetPrefix()
    {
        return 'map';
    }
}