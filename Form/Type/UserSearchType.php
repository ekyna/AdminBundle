<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceSearchType;
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
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'class'    => 'ekyna_admin.user',
                'required' => true,
                'roles'    => ['ROLE_USER'],
            ])
            ->setAllowedTypes('roles', 'array')
            ->setNormalizer('search_parameters', function (Options $options, $value) {
                if (!isset($value['roles'])) {
                    $value['roles'] = $options['roles'];
                }

                return $value;
            });
    }

    public function getParent(): string
    {
        return ResourceSearchType::class;
    }
}
