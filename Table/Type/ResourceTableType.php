<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;

/**
 * Class ResourceTableType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author �tienne Dauvergne <contact@ekyna.com>
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
        ));
    }
}
