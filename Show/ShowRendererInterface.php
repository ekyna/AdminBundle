<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show;

/**
 * Interface ShowRendererInterface
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShowRendererInterface
{
    /**
     * Renders the row.
     *
     * @param mixed  $data    The data to render
     * @param string $name    The type's name
     * @param array  $options The type's options
     *
     * @return string
     */
    public function renderRow($data, string $name, array $options = []): string;

    /**
     * Renders the widget.
     *
     * @param mixed  $data    The data to render
     * @param string $name    The type's name
     * @param array  $options The type's options
     *
     * @return string
     */
    public function renderWidget($data, string $name, array $options = []): string;
}
