<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Extension\AbstractExtension;
use Ekyna\Bundle\AdminBundle\Show\Type\TypeInterface;

use function is_subclass_of;

/**
 * Class CoreExtension
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CoreExtension extends AbstractExtension
{
    private array $classes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->classes = [
            'boolean'    => Type\BooleanType::class,
            'collection' => Type\CollectionType::class,
            'color'      => Type\ColorType::class,
            'datetime'   => Type\DateTimeType::class,
            'entity'     => Type\EntityType::class,
            'link'       => Type\LinkType::class,
            'map'        => Type\MapType::class,
            'number'     => Type\NumberType::class,
            'textarea'   => Type\TextAreaType::class,
            'text'       => Type\TextType::class,
            'tinymce'    => Type\TinyMceType::class,
            'upload'     => Type\UploadType::class,
            'url'        => Type\UrlType::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function hasType(string $name): bool
    {
        return isset($this->classes[$name]);
    }

    /**
     * @inheritDoc
     */
    protected function loadType(string $name): TypeInterface
    {
        $class = $this->classes[$name];

        if (!is_subclass_of($class, TypeInterface::class)) {
            throw new InvalidArgumentException("Class '$class' must implements " . TypeInterface::class);
        }

        return new $class();
    }
}
