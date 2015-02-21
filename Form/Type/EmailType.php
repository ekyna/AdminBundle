<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints;

/**
 * Class EmailType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EmailType extends AbstractType
{
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults(array(
            'constraints' => array(
                new Constraints\NotBlank(),
                new Constraints\Email(),
            ),
        ));
    }

    public function getParent()
    {
        return 'text';
    }

    public function getName()
    {
        return 'ekyna_admin_email';
    }
}
