<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Exception\UnexpectedTypeException;
use Ekyna\Bundle\AdminBundle\Show\Extension\ExtensionInterface;

use function array_unshift;
use function get_class;
use function in_array;

/**
 * Class ShowRegistry
 * @package Ekyna\Bundle\AdminBundle\Show
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class ShowRegistry implements ShowRegistryInterface
{
    /**
     * @var Extension\ExtensionInterface[]
     */
    private array $extensions = [];

    /**
     * @var Type\TypeInterface[]
     */
    private array $types = [];

    /**
     * @var string[]
     */
    private array $templates = [];


    /**
     * Constructor.
     *
     * @param Extension\ExtensionInterface[] $extensions
     */
    public function __construct(array $extensions)
    {
        foreach ($extensions as $extension) {
            if (!$extension instanceof ExtensionInterface) {
                throw new UnexpectedTypeException($extension, ExtensionInterface::class);
            }
        }

        $this->extensions = $extensions;
    }

    /**
     * @inheritDoc
     */
    public function registerExtension(Extension\ExtensionInterface $extension): self
    {
        $class = get_class($extension);

        if (isset($this->extensions[$class])) {
            throw new InvalidArgumentException("Show extension '$class' is already registered.");
        }

        $this->extensions[] = $class;

        return $this;
    }

    /**
     * @inheritDoc
     */
    public function registerTemplates(array $names): self
    {
        foreach ($names as $name) {
            if (in_array($name, $this->templates, true)) {
                throw new InvalidArgumentException("Show template '$name' is already registered.");
            }

            array_unshift($this->templates, $name);
        }


        return $this;
    }

    /**
     * @inheritDoc
     */
    public function getType(string $name): Type\TypeInterface
    {
        if (isset($this->types[$name])) {
            return $this->types[$name];
        }

        foreach ($this->extensions as $extension) {
            if ($extension->hasType($name)) {
                return $this->types[$name] = $extension->getType($name);
            }
        }

        throw new InvalidArgumentException("Type '$name' is not registered.");
    }

    /**
     * @inheritDoc
     */
    public function getTemplates(): array
    {
        return $this->templates;
    }
}
