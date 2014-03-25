<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;

/**
 * PoolBuilder
 */
class PoolBuilder
{
    protected $container;
    protected $prefix;
    protected $resourceName;
    protected $config;

    public function __construct(ContainerBuilder $container, $prefix, $resourceName, array $config)
    {
        $this->container = $container;
        $this->prefix = $prefix;
        $this->resourceName = $resourceName;
        $this->config = $config;
    }

    public function build()
    {
        $configurationKey = $this->getContainerKey('configuration');
        $this->container->setDefinition(
            $configurationKey,
            $this->getConfigurationDefinition()
        );

        $this->container->setDefinition(
            $this->getContainerKey('controller'),
            $this->getControllerDefinition($configurationKey)
        );

        $this->container->setDefinition(
            $this->getContainerKey('repository'),
            $this->getRepositoryDefinition($this->getServiceClass('repository'))
        );

        if(!$this->container->hasDefinition($this->getContainerKey('form'))) {
            $this->createFormDefinition();
        }

        if(!$this->container->hasDefinition($this->getContainerKey('table'))) {
            $this->createTableDefinition();
        }

        $this->setManagerAlias();
    }

    protected function getConfigurationDefinition()
    {
        $templates = isset($this->config['templates']) ? $this->config['templates'] : null; 
        $parentId = isset($this->config['parent']) ? $this->config['parent'] : null;

        $id = sprintf('%s_%s', $this->prefix, $this->resourceName);

        $definition = new Definition('Ekyna\Bundle\AdminBundle\Pool\Configuration');
        $definition
            ->setFactoryService('ekyna_admin.pool_factory')
            ->setFactoryMethod('createConfiguration')
            ->setArguments(array(
                $id,
                $this->config['entity'],
                $this->resourceName,
                $templates,
                $parentId
            ))
            ->addTag('ekyna_admin.configuration', array('alias' => $id))
        ;
        return $definition;
    }

    protected function getControllerDefinition($configurationKey)
    {
        $default = 'Ekyna\Bundle\AdminBundle\Controller\ResourceController';

        $definition = new Definition($this->getServiceClass('controller', $default));

        $definition
            ->setArguments(array(new Reference($configurationKey)))
            ->addMethodCall('setContainer', array(new Reference('service_container')))
        ;

        return $definition;
    }

    protected function getClassMetadataDefinition($entity)
    {
        $definition = new Definition($this->getClassMetadataClassname());
        $definition
            ->setFactoryService($this->getManagerServiceKey())
            ->setFactoryMethod('getClassMetadata')
            ->setArguments(array($entity))
            ->setPublic(false)
        ;

        return $definition;
    }

    protected function getClassMetadataClassname()
    {
        return 'Doctrine\\ORM\\Mapping\\ClassMetadata';
    }

    protected function getRepositoryDefinition()
    {
        $default = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository';

        $definition = new Definition($this->getServiceClass('repository', $default));

        $definition->setArguments(array(
            new Reference($this->getContainerKey('manager')),
            $this->getClassMetadataDefinition($this->config['entity'])
        ));

        return $definition;
    }

    protected function createFormDefinition()
    {
        if(null !== $class = $this->getServiceClass('form')) {
            $key = $this->getContainerKey('form');
            if(!$this->container->has($key)) {
                $definition = new Definition($class);
                $definition
                    ->setArguments(array($this->config['entity']))
                    ->addTag('form.type', array('alias' => sprintf('%s_%s', $this->prefix, $this->resourceName)))
                ;
                $this->container->setDefinition($this->getContainerKey('form'), $definition);
            }
        }
    }

    protected function createTableDefinition()
    {
        if(null !== $class = $this->getServiceClass('table')) {
            $key = $this->getContainerKey('table');
            if(!$this->container->has($key)) {
                $definition = new Definition($class);
                $definition
                    ->setArguments(array($this->config['entity']))
                    ->addTag('table.type', array('alias' => sprintf('%s_%s', $this->prefix, $this->resourceName)))
                ;
                $this->container->setDefinition($key, $definition);
            }
        }
    }

    protected function setManagerAlias()
    {
        $this->container->setAlias(
            $this->getContainerKey('manager'),
            new Alias($this->getManagerServiceKey())
        );
    }

    protected function getManagerServiceKey()
    {
        return 'doctrine.orm.entity_manager';
    }

    protected function getContainerKey($service, $suffix = null)
    {
        return sprintf('%s_%s.%s%s', $this->prefix, $this->resourceName, $service, $suffix);
    }

    protected function getServiceClass($service, $default = null)
    {
        $class = $default;
        $parameter = $this->getContainerKey($service, '.class');
        if ($this->container->hasParameter($parameter)) {
            $class = $this->container->getParameter($parameter);
        }
        if(isset($this->config[$service])) {
            $class = $this->config[$service];
        }
        return $class;
    }
}