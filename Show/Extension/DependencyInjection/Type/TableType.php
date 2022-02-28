<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type;

use Doctrine\Common\Collections\Collection;
use Ekyna\Bundle\AdminBundle\Show\Type\AbstractType;
use Ekyna\Bundle\AdminBundle\Show\View;
use Ekyna\Component\Commerce\Exception\RuntimeException;
use Ekyna\Component\Resource\Config\Registry\ResourceRegistryInterface;
use Ekyna\Component\Table\TableFactoryInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function sprintf;
use function Symfony\Component\Translation\t;

/**
 * Class TableType
 * @package Ekyna\Bundle\AdminBundle\Show\Extension\DependencyInjection\Type
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class TableType extends AbstractType
{
    private ResourceRegistryInterface $resourceRegistry;
    private TableFactoryInterface     $tableFactory;
    private RequestStack              $requestStack;

    public function __construct(
        ResourceRegistryInterface $resourceRegistry,
        TableFactoryInterface     $tableFactory,
        RequestStack              $requestStack
    ) {
        $this->resourceRegistry = $resourceRegistry;
        $this->tableFactory = $tableFactory;
        $this->requestStack = $requestStack;
    }

    /**
     * @inheritDoc
     */
    public function build(View $view, $value, array $options = []): void
    {
        parent::build($view, $value, $options);

        $rCfg = $this->resourceRegistry->find($options['resource']);

        if (!$type = $rCfg->getData('table')) {
            throw new RuntimeException(sprintf(
                "No table type configured for resource '%s'.",
                $options['resource']
            ));
        }

        if ($value instanceof Collection) {
            $value = $value->toArray();
        }

        $table = $this
            ->tableFactory
            ->createTable('table', $type, [
                'source' => $value,
            ]);

        $request = $this->requestStack->getCurrentRequest();
        if ($response = $table->handleRequest($request)) {
            // TODO return $response;
        }

        $view->vars['table'] = $table->createView();
    }

    public static function getName(): string
    {
        return 'table';
    }

    protected function configureOptions(OptionsResolver $resolver): void
    {
        $resolver
            ->setRequired([
                'resource',
            ])
            ->setDefault('label', function (Options $options, $value) {
                if ($value) {
                    return $value;
                }

                $rCfg = $this->resourceRegistry->find($options['resource']);

                return t($rCfg->getResourceLabel(true), [], $rCfg->getTransDomain());
            })
            ->setAllowedTypes('resource', 'string');
    }
}
