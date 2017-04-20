<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show;

/**
 * Interface ShowRegistryInterface
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface ShowRegistryInterface
{
    /**
     * Registers the extension.
     *
     * @param Extension\ExtensionInterface $extension
     *
     * @return self
     */
    public function registerExtension(Extension\ExtensionInterface $extension): self;

    /**
     * Registers the templates.
     *
     * @param array $names
     *
     * @return self
     */
    public function registerTemplates(array $names): self;

    /**
     * Returns the type by his name.
     *
     * @param string $name
     *
     * @return Type\TypeInterface
     */
    public function getType(string $name): Type\TypeInterface;

    /**
     * Returns the registered templates.
     *
     * @return array
     */
    public function getTemplates(): array;
}
