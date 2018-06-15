<?php

namespace Ekyna\Bundle\AdminBundle\Show\Extension\Core;

use Ekyna\Bundle\AdminBundle\Show\Exception\InvalidArgumentException;
use Ekyna\Bundle\AdminBundle\Show\Extension\AbstractExtension;
use Ekyna\Bundle\AdminBundle\Show\Type\TypeInterface;

/**
 * Class CoreExtension
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\Core
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class CoreExtension extends AbstractExtension
{
    /**
     * @var array
     */
    private $classes;


    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->classes = [
            'boolean'      => Type\BooleanType::class,
            'collection'   => Type\CollectionType::class,
            'color'        => Type\ColorType::class,
            'choice'       => Type\ChoiceType::class,
            'datetime'     => Type\DateTimeType::class,
            'entity'       => Type\EntityType::class,
            'map'          => Type\MapType::class,
            'number'       => Type\NumberType::class,
            'text'         => Type\TextType::class,
            'textarea'     => Type\TextAreaType::class,
            'tinymce'      => Type\TinyMceType::class,
            'translations' => Type\TranslationsType::class,
            'upload'       => Type\UploadType::class,
            'url'          => Type\UrlType::class,
        ];
    }

    /**
     * @inheritDoc
     */
    public function hasType($name)
    {
        return isset($this->classes[$name]);
    }

    /**
     * @inheritDoc
     */
    protected function loadType($name)
    {
        $class = $this->classes[$name];

        if (!in_array(TypeInterface::class, class_implements($class), true)) {
            throw new InvalidArgumentException("Class '$class' must implements " . TypeInterface::class);
        }

        return new $class;
    }
}
