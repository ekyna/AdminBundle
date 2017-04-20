<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Form\Type;

use Doctrine\ORM\EntityRepository;
use Ekyna\Bundle\ResourceBundle\Form\Type\ResourceChoiceType;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserChoiceType
 * @package Ekyna\Bundle\AdminBundle\Form\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserChoiceType extends AbstractType
{
    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'resource'      => 'ekyna_admin.user',
                'roles'         => ['ROLE_ADMIN'],
                'select2'       => false,
                'query_builder' => function (Options $options, $value) {
                    if (null !== $value) {
                        return $value;
                    }

                    return function (EntityRepository $repository) use ($options) {
                        $qb = $repository
                            ->createQueryBuilder('u')
                            ->join('u.group', 'g')
                            ->orderBy('u.firstName', 'ASC')
                            ->orderBy('u.lastName', 'ASC');

                        $expr = $qb->expr();

                        $roles = $options['roles'];
                        if (1 == count($roles)) {
                            $qb->andWhere($expr->like('g.roles', $expr->literal('%' . $roles[0] . '%')));
                        } elseif (!empty($roles)) {
                            $orRoles = $expr->orX();
                            foreach ($roles as $role) {
                                $orRoles->add($expr->like('g.roles', $expr->literal('%' . $role . '%')));
                            }
                            $qb->andWhere($orRoles);
                        }

                        return $qb;
                    };
                },
            ])
            ->setAllowedTypes('roles', 'array');
    }

    public function getParent(): string
    {
        return ResourceChoiceType::class;
    }
}
