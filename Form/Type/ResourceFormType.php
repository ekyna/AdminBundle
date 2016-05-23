<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceFormType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
abstract class ResourceFormType extends AbstractType
{
    /**
     * @var string
     */
    protected $dataClass;

    /**
     * Constructor.
     *
     * @param $class
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
        $resolver->setDefaults([
            'data_class' => $this->dataClass,
        ]);
    }
}
