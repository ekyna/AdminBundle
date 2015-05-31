<?php

namespace Ekyna\Bundle\AdminBundle\Model;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class AbstractTranslatable
 * @package Ekyna\Bundle\AdminBundle\Model
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractTranslatable implements TranslatableInterface
{
    use TranslatableTrait;

    /**
     * Constructor.
     */
    public function __construct()
    {
        $this->translations = new ArrayCollection();
    }
}
