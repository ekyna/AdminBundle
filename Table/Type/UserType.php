<?php

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Service\Security\UserProviderInterface;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class UserType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserType extends ResourceTableType
{
    /**
     * @var UserProviderInterface
     */
    protected $userProvider;


    /**
     * Constructor.
     *
     * @param UserProviderInterface $userProvider
     * @param string                $userClass
     */
    public function __construct(UserProviderInterface $userProvider, string $userClass)
    {
        parent::__construct($userClass);

        $this->userProvider = $userProvider;
    }

    /**
     * {@inheritdoc}
     */
    public function buildTable(TableBuilderInterface $builder, array $options)
    {
        $group = $this->getUserGroup();

        $builder
            ->addColumn('email', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_admin.user.label.singular',
                'route_name'           => 'ekyna_admin_user_admin_show',
                'route_parameters_map' => ['userId' => 'id'],
                'position'             => 10,
            ]);

        if (null !== $group) {
            $builder->addColumn('group', BType\Column\AnchorType::class, [
                'label'                => 'ekyna_core.field.group',
                'property_path'        => 'group.name',
                'route_name'           => 'ekyna_admin_group_admin_show',
                'route_parameters_map' => ['groupId' => 'group.id'],
                'position'             => 20,
            ]);
        }

        $builder
            ->addColumn('active', CType\Column\BooleanType::class, [
                'label'                => 'ekyna_core.field.enabled',
                'sortable'             => true,
                'route_name'           => 'ekyna_admin_user_admin_toggle',
                'route_parameters'     => ['field' => 'active'],
                'route_parameters_map' => ['userId' => 'id'],
                'position'             => 30,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'buttons' => [
                    [
                        'label'                => 'ekyna_core.button.edit',
                        'class'                => 'warning',
                        'route_name'           => 'ekyna_admin_user_admin_edit',
                        'route_parameters_map' => ['userId' => 'id'],
                        'permission'           => 'edit',
                    ],
                    [
                        'label'                => 'ekyna_core.button.remove',
                        'class'                => 'danger',
                        'route_name'           => 'ekyna_admin_user_admin_remove',
                        'route_parameters_map' => ['userId' => 'id'],
                        'permission'           => 'delete',
                    ],
                ],
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => 'ekyna_core.field.email',
                'position' => 10,
            ]);

        if (null !== $group) {
            $builder
                ->addFilter('group', ResourceType::class, [
                    //'label'         => 'ekyna_core.field.group',
                    'resource'      => 'ekyna_admin.group',
                    'entity_label'  => 'name',
                    'query_builder' => function (EntityRepository $er) use ($group) {
                        $qb = $er->createQueryBuilder('g');

                        return $qb->andWhere($qb->expr()->gte('g.position', $group->getPosition()));
                    },
                    'position'      => 20,
                ]);
        }

        $builder
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => 'ekyna_core.field.enabled',
                'position' => 30,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => 'ekyna_core.field.created_at',
                'position' => 40,
            ]);
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        parent::configureOptions($resolver);

        $resolver->setNormalizer('source', function (
            /** @noinspection PhpUnusedParameterInspection */
            Options $options,
            $value
        ) {
            if (null !== $group = $this->getUserGroup()) {
                if ($value instanceof EntitySource) {
                    $value->setQueryBuilderInitializer(function (QueryBuilder $qb, $alias) use ($group) {
                        $qb
                            ->join($alias . '.group', 'g')
                            ->andWhere($qb->expr()->gte('g.position', $group->getPosition()));
                    });
                }
            }

            return $value;
        });
    }

    /**
     * Returns the current user's group.
     *
     * @return \Ekyna\Bundle\AdminBundle\Model\GroupInterface|null
     */
    private function getUserGroup()
    {
        if (null !== $user = $this->userProvider->getUser()) {
            return $user->getGroup();
        }

        return null;
    }
}
