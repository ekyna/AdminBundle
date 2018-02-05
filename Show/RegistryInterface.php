<?php

namespace Ekyna\Bundle\AdminBundle\Show;

/**
 * Interface RegistryInterface
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
interface RegistryInterface
{
    /**
     * Registers the extension.
     *
     * @param Extension\ExtensionInterface $extension
     *
     * @return self
     */
    public function registerExtension(Extension\ExtensionInterface $extension);

    /**
     * Registers the templates.
     *
     * @param array $names
     *
     * @return self
     */
    public function registerTemplates(array $names);

    /**
     * Returns the type by his name.
     *
     * @param string $name
     *
     * @return Type\TypeInterface
     */
    public function getType($name);

    /**
     * Returns the registered templates.
     *
     * @return array
     */
    public function getTemplates();
}