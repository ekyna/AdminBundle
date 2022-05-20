<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Table\Type\Column;

use Ekyna\Component\Resource\Action\Permission;
use Ekyna\Component\Table\Bridge\Doctrine\ORM\Type\Column\EntityType;
use Ekyna\Component\Table\Column\ColumnInterface;
use Ekyna\Component\Table\Extension\AbstractColumnTypeExtension;
use Ekyna\Component\Table\Source\RowInterface;
use Ekyna\Component\Table\View\CellView;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

use function array_diff_key;
use function count;

/**
 * Class EntityTypeExtension
 * @package Ekyna\Bundle\AdminBundle\Table\Type\Column
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class EntityTypeExtension extends AbstractColumnTypeExtension
{
    private AuthorizationCheckerInterface $authorization;
    private UrlGeneratorInterface         $urlGenerator;

    public function __construct(
        AuthorizationCheckerInterface $authorization,
        UrlGeneratorInterface         $urlGenerator
    ) {
        $this->authorization = $authorization;
        $this->urlGenerator = $urlGenerator;
    }

    public function buildCellView(CellView $view, ColumnInterface $column, RowInterface $row, array $options): void
    {
        $view->vars['block_prefix'] = 'entity';

        if (empty($route = $options['route'])) {
            return;
        }

        $accessor = $row->getPropertyAccessor();
        $entities = $view->vars['value'];

        foreach ($entities as &$entity) {
            $value = $entity['value'];
            if (!$this->authorization->isGranted(Permission::READ, $value)) {
                continue;
            }

            $parameters = $options['parameters'];
            if (!empty($options['parameters_map'])) {
                foreach ($options['parameters_map'] as $parameter => $propertyPath) {
                    if (null !== $value = $accessor->getValue($value, $propertyPath)) {
                        $parameters[$parameter] = $value;
                    }
                }

                if (0 < count(array_diff_key($options['parameters_map'], $parameters))) {
                    continue;
                }
            }

            $entity['tag'] = 'a';
            $entity['attr'] = [
                'href' => $this->urlGenerator->generate($route, $parameters),
            ];
        }

        $view->vars['value'] = $entities;
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setDefaults([
                'route'          => null,
                'parameters'     => [],
                'parameters_map' => [],
            ])
            ->setAllowedTypes('route', ['null', 'string'])
            ->setAllowedTypes('parameters', 'array')
            ->setAllowedTypes('parameters_map', 'array');
    }

    public static function getExtendedTypes(): array
    {
        return [EntityType::class];
    }
}
