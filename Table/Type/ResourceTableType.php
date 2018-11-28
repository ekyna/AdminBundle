<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Ekyna\Component\Table\AbstractTableType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ResourceTableType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 *
 * @TODO Move to resource bundle
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
        $resolver->setDefaults([
            'source' => new EntitySource($this->dataClass),
        ]);
    }
}
