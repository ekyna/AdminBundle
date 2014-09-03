<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

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
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $resolver->setDefaults(array(
            'data_class' => $this->dataClass,
            'selector_config' => array(
                'property_path' => 'id',
                'data_map' => array('id', 'name' => null),
            )
        ));
    }
}
