<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show;

/**
 * Class View
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class View
{
    /**
     * The variables assigned to this view.
     *
     * @var array
     */
    public array $vars = [
        'value' => null,
        'attr'  => [],
    ];
}
