<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceTableType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class ResourceTableType extends AbstractTableType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * @param string $class
     */
    public function __construct($class)
    {
        $this->dataClass = $class;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setDefaults([
            'data_class' => $this->dataClass,
            'selector_config' => [
                'property_path' => 'id',
                'data_map' => ['id', 'name' => null],
            ]
        ]);
    }
}
