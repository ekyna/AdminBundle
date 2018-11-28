<?php

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\CoreBundle\Form\Type\EntitySearchType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserSearchType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserSearchType extends AbstractType
{
    /**
     * @var string
     */
    private $userClass;


    /**
     * Constructor.
     *
     * @param string $userClass
     */
    public function __construct($userClass)
    {
        $this->userClass = $userClass;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $resolver
            ->setDefaults([
                'label'    => 'ekyna_admin.user.label.singular',
                'class'    => $this->userClass,
                'route'    => 'ekyna_admin_user_admin_search',
                'required' => true,
                'roles'    => ['ROLE_USER'],
            ])
            ->setAllowedTypes('roles', 'array')
            ->setNormalizer('route_params', function (Options $options, $value) {
                if (!isset($value['roles'])) {
                    $value['roles'] = $options['roles'];
                }

                return $value;
            });
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return EntitySearchType::class;
    }
}
