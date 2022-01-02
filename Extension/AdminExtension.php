<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\Extension;

use Ekyna\Bundle\ResourceBundle\Form\Type\AbstractResourceType as ResourceFormType;
use Ekyna\Bundle\ResourceBundle\Table\Type\AbstractResourceType as ResourceTableType;
use Ekyna\Component\Resource\Config\Factory\RegistryFactoryInterface;
use Ekyna\Component\Resource\Config\Resolver\DefaultsResolver;
use Ekyna\Component\Resource\Config\ResourceConfig;
use Ekyna\Component\Resource\Extension\AbstractExtension;
use Ekyna\Component\Table\TableTypeInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

use function is_subclass_of;
use function iterator_to_array;

/**
 * Class AdminExtension
 * @package Ekyna\Bundle\AdminBundle\Extension
 * @author  Etienne Dauvergne <contact@ekyna.com>
 */
class AdminExtension extends AbstractExtension
{
    private const FORM_SERVICE_ID  = '%s.form_type.%s';
    private const TABLE_SERVICE_ID = '%s.table_type.%s';


    public function extendResourceConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'form'  => null,
            'table' => null,
        ];

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add($options);

        $resolver
            ->setDefaults($options)
            ->setAllowedTypes('form', ['null', 'string'])
            ->setAllowedTypes('table', ['null', 'string'])
            ->setAllowedValues('form', function ($value) {
                return $this->validateClass($value, FormTypeInterface::class, false);
            })
            ->setAllowedValues('table', function ($value) {
                return $this->validateClass($value, TableTypeInterface::class, false);
            });
    }

    public function extendActionConfig(OptionsResolver $resolver, DefaultsResolver $defaults): void
    {
        $options = [
            'theme'        => 'default',
            'icon'         => null,
            'trans_domain' => 'EkynaUi',
        ];

        $buttonResolver = new OptionsResolver();
        $buttonResolver
            ->setRequired('label')
            ->setDefaults($options)
            ->setAllowedTypes('label', 'string')
            ->setAllowedTypes('theme', 'string')
            ->setAllowedTypes('icon', ['string', 'null'])
            ->setAllowedTypes('trans_domain', ['string', 'null']);

        /** @noinspection PhpUnhandledExceptionInspection */
        $defaults->add([
            'button' => $options,
        ]);

        $resolver
            ->setDefined('button')
            ->setAllowedTypes('button', ['array', 'null'])
            ->setNormalizer(
                'button',
                function (Options $options, $value) use ($buttonResolver): ?array {
                    if (empty($value)) {
                        return null;
                    }

                    return $buttonResolver->resolve($value);
                }
            );
    }

    public function extendActionOptions(OptionsResolver $resolver): void
    {
        $buttonResolver = new OptionsResolver();
        $buttonResolver
            ->setDefined(['label', 'theme', 'icon', 'trans_domain'])
            ->setAllowedTypes('label', ['string', 'null'])
            ->setAllowedTypes('theme', ['string', 'null'])
            ->setAllowedTypes('icon', ['string', 'null'])
            ->setAllowedTypes('trans_domain', ['string', 'null']);

        $resolver
            ->setDefined('button')
            ->setAllowedTypes('button', ['array', 'null'])
            ->setNormalizer(
                'button',
                function (Options $options, $value) use ($buttonResolver): ?array {
                    if (empty($value)) {
                        return null;
                    }

                    return $buttonResolver->resolve($value);
                }
            );
    }

    public function configureContainer(ContainerBuilder $container, RegistryFactoryInterface $factory): void
    {
        /** @var ResourceConfig[] $resources */
        $resources = iterator_to_array($factory->getResourceRegistry()->all());

        foreach ($resources as $resource) {
            // Configures the form types
            $this->configureFormTypes($container, $resource);

            // Configures the table type
            $this->configureTableType($container, $resource);
        }
    }

    /**
     * Configures the resource form types.
     */
    private function configureFormTypes(ContainerBuilder $container, ResourceConfig $resource): void
    {
        // Resource form type
        if (null === $class = $resource->getData('form')) {
            return;
        }

        $id = sprintf(self::FORM_SERVICE_ID, $resource->getNamespace(), $resource->getName());
        if ($container->has($id)) {
            $definition = $container->getDefinition($id);
        } else {
            $container->setDefinition($id, $definition = new Definition($class));
        }

        if ($class !== $definition->getClass()) {
            $definition->setClass($class);
        }

        if (!$definition->hasTag('form.type')) {
            $definition->addTag('form.type');
        }

        if (!is_subclass_of($definition->getClass(), ResourceFormType::class, true)) {
            return;
        }

        if ($definition->hasMethodCall('setDataClass')) {
            return;
        }

        $definition->addMethodCall('setDataClass', [$resource->getEntityClass()]);
    }

    /**
     * Configures the resource table type.
     */
    private function configureTableType(ContainerBuilder $container, ResourceConfig $resource): void
    {
        if (null === $class = $resource->getData('table')) {
            return;
        }

        $id = sprintf(self::TABLE_SERVICE_ID, $resource->getNamespace(), $resource->getName());
        if ($container->has($id)) {
            $definition = $container->getDefinition($id);
        } else {
            $container->setDefinition($id, $definition = new Definition($class));
        }

        if ($class !== $definition->getClass()) {
            $definition->setClass($class);
        }

        if (!$definition->hasTag('table.type')) {
            $definition->addTag('table.type');
        }

        if (!is_subclass_of($definition->getClass(), ResourceTableType::class, true)) {
            return;
        }

        if ($definition->hasMethodCall('setDataClass')) {
            return;
        }

        $definition->addMethodCall('setDataClass', [$resource->getEntityClass()]);
    }
}
