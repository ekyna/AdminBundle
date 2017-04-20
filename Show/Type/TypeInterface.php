<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Type;

use Ekyna\Bundle\AdminBundle\Show\View;

/**
 * Interface TypeInterface
 * @package Ekyna\Bundle\AdminBundle\Show\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface TypeInterface
{
    /**
     * Resolve the options.
     *
     * @param array $options The options to resolve.
     *
     * @return array The resolved options.
     */
    public function resolveOptions(array $options = []): array;

    /**
     * Builds the view.
     *
     * @param View  $view    The view to build
     * @param mixed $value   The value to render
     * @param array $options The resolved options
     */
    public function build(View $view, $value, array $options = []): void;

    /**
     * Returns the row prefix.
     *
     * @return string|null
     */
    public function getRowPrefix(): ?string;

    /**
     * Returns the widget prefix.
     *
     * @return string|null
     */
    public function getWidgetPrefix(): ?string;

    /**
     * Returns the type name, which can be used in templates.
     *
     * @return string
     */
    public static function getName(): string;
}
