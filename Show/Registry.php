<?php

namespace Ekyna\Bundle\AdminBundle\Show;

use Ekyna\Bundle\AdminBundle\Show\Extension\ExtensionInterface;

/**
 * Class Registry
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class Registry implements RegistryInterface
{
    /**
     * @var Extension\ExtensionInterface[]
     */
    private $extensions = [];

    /**
     * @var Type\TypeInterface[]
     */
    private $types = [];

    /**
     * @var string[]
     */
    private $templates = [];


    /**
     * Constructor.
     *
     * @param Extension\ExtensionInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof ExtensionInterface) {
                throw new Exception\InvalidArgumentException(
                    "Expected instance of " . ExtensionInterface::class
                );
            }
        }

        $this->extensions = $extensions;
    }

    /**
     * @inheritDoc
     */
    public function registerExtension(Extension\ExtensionInterface $extension)
    {
        $class = get_class($extension);

        if (isset($this->extensions[$class])) {
            throw new \InvalidArgumentException("Show extension '$class' is already registered.");
        }

        $this->extensions[] = $class;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerTemplates(array $names)
    {
        foreach ($names as $name) {
            if (in_array($name, $this->templates, true)) {
                throw new \InvalidArgumentException("Show template '$name' is already registered.");
            }

            array_unshift($this->templates, $name);
        }


        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType($name)
    {
        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        foreach ($this->extensions as $extension) {
            if ($extension->hasType($name)) {
                return $this->types[$name] = $extension->getType($name);
            }
        }

        throw new \InvalidArgumentException("Type '$name' is not registered.");
    }

    /**
     * @inheritDoc
     */
    public function getTemplates()
    {
        return $this->templates;
    }
}