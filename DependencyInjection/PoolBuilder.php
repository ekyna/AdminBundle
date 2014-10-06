<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;

/**
 * PoolBuilder
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PoolBuilder
{
    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var string
     */
    private $prefix;

    /**
     * @var string
     */
    private $resourceName;

    /**
     * @var array
     */
    private $config;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container
     * @param string $prefix
     * @param string $resourceName
     * @param array $config
     */
    public function __construct(ContainerBuilder $container, $prefix, $resourceName, array $config)
    {
        $this->container = $container;
        $this->prefix = $prefix;
        $this->resourceName = $resourceName;
        $this->config = $config;
    }

    /**
     * Builds the container.
     */
    public function build()
    {
        $this->container->setParameter($this->getServiceId('class'), $this->config['entity']);

        $configurationKey = $this->getServiceId('configuration');
        $this->container->setDefinition(
            $configurationKey,
            $this->getConfigurationDefinition()
        );

        $this->container->setDefinition(
            $this->getServiceId('controller'),
            $this->getControllerDefinition($configurationKey)
        );

        if (!$this->container->hasDefinition($this->getServiceId('manager'))) {
            $this->setManagerAlias();
        }

        $this->container->setDefinition(
            $this->getServiceId('repository'),
            $this->getRepositoryDefinition()
        );

        if (!$this->container->hasAlias($this->getServiceId('operator'))) {
            $this->container->setDefinition(
                $this->getServiceId('operator'),
                $this->getOperatorDefinition()
            );
        }

        if (!$this->container->hasDefinition($this->getServiceId('form_type'))) {
            $this->createFormDefinition();
        }

        if (!$this->container->hasDefinition($this->getServiceId('table_type'))) {
            $this->createTableDefinition();
        }
    }

    /**
     * Returns the Configuration service definition.
     *
     * @return Definition
     */
    private function getConfigurationDefinition()
    {
        $templates = isset($this->config['templates']) ? $this->config['templates'] : null;
        $parentId = isset($this->config['parent']) ? $this->config['parent'] : null;

        $definition = new Definition('Ekyna\Bundle\AdminBundle\Pool\Configuration');
        $definition
            ->setFactoryService('ekyna_admin.pool_factory')
            ->setFactoryMethod('createConfiguration')
            ->setArguments(array(
                $this->prefix,
                $this->resourceName,
                $this->config['entity'],
                $templates,
                $parentId
            ))
            ->addTag('ekyna_admin.configuration', array(
                'alias' => sprintf('%s_%s', $this->prefix, $this->resourceName))
            );

        return $definition;
    }

    /**
     * Returns the Controller service definition.
     *
     * @param $configurationKey
     *
     * @return Definition
     */
    private function getControllerDefinition($configurationKey)
    {
        $default = 'Ekyna\Bundle\AdminBundle\Controller\ResourceController';

        $definition = new Definition($this->getServiceClass('controller', $default));

        $definition
            ->setArguments(array(new Reference($configurationKey)))
            ->addMethodCall('setContainer', array(new Reference('service_container')));

        return $definition;
    }

    /**
     * Returns the ClassMetadata service definition.
     *
     * @param $entity
     *
     * @return Definition
     */
    private function getClassMetadataDefinition($entity)
    {
        $definition = new Definition($this->getClassMetadataClassname());
        $definition
            ->setFactoryService($this->getManagerServiceId())
            ->setFactoryMethod('getClassMetadata')
            ->setArguments(array($entity))
            ->setPublic(false);

        return $definition;
    }

    /**
     * Returns the ClassMetadata fqcn.
     *
     * @return string
     */
    private function getClassMetadataClassname()
    {
        return 'Doctrine\ORM\Mapping\ClassMetadata';
    }

    /**
     * Returns the operator service definition.
     *
     * @TODO Swap with ResourceManager when ready.
     * @return Definition
     */
    private function getOperatorDefinition()
    {
        $default = 'Ekyna\Bundle\AdminBundle\Operator\ResourceOperator';

        $definition = new Definition($this->getServiceClass('operator', $default));

        $definition->setArguments(array(
            new Reference($this->getManagerServiceId()),
            new Reference($this->getEventDispatcherServiceId()),
            new Reference($this->getServiceId('configuration')),
        ));

        return $definition;
    }

    /**
     * Returns the Repository service definition.
     *
     * @return Definition
     */
    private function getRepositoryDefinition()
    {
        $default = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository';

        $definition = new Definition($this->getServiceClass('repository', $default));

        $definition->setArguments(array(
            new Reference($this->getServiceId('manager')),
            $this->getClassMetadataDefinition($this->config['entity'])
        ));

        return $definition;
    }

    /**
     * Creates the Form service definition.
     */
    private function createFormDefinition()
    {
        if (null !== $class = $this->getServiceClass('form')) {
            $key = $this->getServiceId('form_type');
            if (!$this->container->has($key)) {
                $definition = new Definition($class);
                $definition
                    ->setArguments(array($this->config['entity']))
                    ->addTag('form.type', array('alias' => sprintf('%s_%s', $this->prefix, $this->resourceName)));
                $this->container->setDefinition($key, $definition);
            }
        }
    }

    /**
     * Creates the Table service definition.
     */
    private function createTableDefinition()
    {
        if (null !== $class = $this->getServiceClass('table')) {
            $key = $this->getServiceId('table_type');
            if (!$this->container->has($key)) {
                $definition = new Definition($class);
                $definition
                    ->setArguments(array($this->config['entity']))
                    ->addTag('table.type', array('alias' => sprintf('%s_%s', $this->prefix, $this->resourceName)));
                $this->container->setDefinition($key, $definition);
            }
        }
    }

    private function setManagerAlias()
    {
        $this->container->setAlias(
            $this->getServiceId('manager'),
            new Alias($this->getManagerServiceId())
        );
    }

    /**
     * Returns the default entity manager service id.
     *
     * @return string
     */
    private function getManagerServiceId()
    {
        return 'doctrine.orm.entity_manager';
    }

    /**
     * Returns the event dispatcher service id.
     *
     * @return string
     */
    private function getEventDispatcherServiceId()
    {
        return 'event_dispatcher';
    }

    /**
     * Returns the service id for the given name.
     *
     * @param string $name
     * @param string $suffix
     *
     * @return string
     */
    private function getServiceId($name, $suffix = null)
    {
        return sprintf('%s.%s.%s%s', $this->prefix, $this->resourceName, $name, $suffix);
    }

    /**
     * Returns the service class for the given name.
     *
     * @param string $name
     * @param string $default
     *
     * @return string|null
     */
    private function getServiceClass($name, $default = null)
    {
        $class = $default;
        $parameter = $this->getServiceId($name, '.class');
        if ($this->container->hasParameter($parameter)) {
            $class = $this->container->getParameter($parameter);
        }
        if (isset($this->config[$name])) {
            $class = $this->config[$name];
        }
        return $class;
    }
}
