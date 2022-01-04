<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use Ekyna\Bundle\AdminBundle\Action\DeleteAction;
use Ekyna\Bundle\AdminBundle\Action\UpdateAction;
use Ekyna\Bundle\AdminBundle\Model\GroupInterface;
use Ekyna\Bundle\ResourceBundle\Table\Filter\ResourceType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType;
use Ekyna\Bundle\TableBundle\Extension\Type as BType;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Source\EntitySource;
use Ekyna\Component\Table\Extension\Core\Type as CType;
use Ekyna\Component\Table\TableBuilderInterface;
use Ekyna\Component\User\Service\UserProviderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function Symfony\Component\Translation\t;

/**
 * Class UserType
 * @package Ekyna\Bundle\AdminBundle\Table\Type
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class UserType extends AbstractResourceType
{
    protected UserProviderInterface $userProvider;

    public function __construct(UserProviderInterface $userProvider)
    {
        $this->userProvider = $userProvider;
    }

    public function buildTable(TableBuilderInterface $builder, array $options): void
    {
        $group = $this->getUserGroup();

        $builder
            ->addColumn('email', BType\Column\AnchorType::class, [
                'label'         => t('user.label.singular', [], 'EkynaAdmin'),
                'property_path' => null,
                'position'      => 10,
            ]);

        if (null !== $group) {
            $builder->addColumn('group', BType\Column\AnchorType::class, [
                'label'         => t('field.group', [], 'EkynaUi'),
                'property_path' => 'group',
                'position'      => 20,
            ]);
        }

        $builder
            ->addColumn('enabled', CType\Column\BooleanType::class, [
                'label'          => t('field.enabled', [], 'EkynaUi'),
                'sortable'       => true,
                'route'          => 'ekyna_admin_user_admin_toggle',
                'parameters'     => ['field' => 'enabled'],
                'parameters_map' => ['userId' => 'id'],
                'position'       => 30,
            ])
            ->addColumn('createdAt', CType\Column\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'sortable' => true,
                'position' => 40,
            ])
            ->addColumn('actions', BType\Column\ActionsType::class, [
                'resource' => $this->dataClass,
                'actions'  => [
                    UpdateAction::class,
                    DeleteAction::class,
                ],
            ])
            ->addFilter('email', CType\Filter\TextType::class, [
                'label'    => t('field.email', [], 'EkynaUi'),
                'position' => 10,
            ]);

        if (null !== $group) {
            $builder
                ->addFilter('group', ResourceType::class, [
                    'resource'      => 'ekyna_admin.group',
                    'entity_label'  => 'name',
                    'query_builder' => function (EntityRepository $repository) use ($group) {
                        $qb = $repository->createQueryBuilder('g');

                        return $qb
                            ->andWhere($qb->expr()->gte('g.position', ':position'))
                            ->setParameter('position', $group->getPosition());
                    },
                    'position'      => 20,
                ]);
        }

        $builder
            ->addFilter('enabled', CType\Filter\BooleanType::class, [
                'label'    => t('field.enabled', [], 'EkynaUi'),
                'position' => 30,
            ])
            ->addFilter('createdAt', CType\Filter\DateTimeType::class, [
                'label'    => t('field.created_at', [], 'EkynaUi'),
                'position' => 40,
            ]);
    }

    /**
     * @inheritDoc
     */
    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $resolver->setNormalizer('source', function (Options $options, $value) {
            if (!$value instanceof EntitySource) {
                return $value;
            }

            if (!$group = $this->getUserGroup()) {
                return $value;
            }

            $value->setQueryBuilderInitializer(function (QueryBuilder $qb, string $alias) use ($group): void {
                $qb
                    ->join($alias . '.group', 'g')
                    ->andWhere($qb->expr()->gte('g.position', ':position'))
                    ->setParameter('position', $group->getPosition());
            });

            return $value;
        });
    }

    /**
     * Returns the current user's group.
     *
     * @return GroupInterface|null
     */
    private function getUserGroup(): ?GroupInterface
    {
        if (!$user = $this->userProvider->getUser()) {
            return null;
        }

        return $user->getGroup();
    }
}
