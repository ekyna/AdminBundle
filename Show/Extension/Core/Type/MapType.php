<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core\Type;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;

use function is_array;

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
    public function build(View $view, $value, array $options = []): void
    {
        if (!is_array($value)) {
            throw new InvalidArgumentException('Expected key/value array.');
        }

        parent::build($view, $value, $options);
    }

    /**
     * @inheritDoc
     */
    public static function getName(): string
    {
        return 'map';
    }
}
