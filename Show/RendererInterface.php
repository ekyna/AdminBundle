<?php

namespace Ekyna\Bundle\AdminBundle\Show;

/**
 * Interface RendererInterface
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RendererInterface
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
    public function renderRow($data, $name, array $options = []);

    /**
     * Renders the widget.
     *
     * @param mixed  $data    The data to render
     * @param string $name    The type's name
     * @param array  $options The type's options
     *
     * @return string
     */
    public function renderWidget($data, $name, array $options = []);
}