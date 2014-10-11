<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class PoolBuilder
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class PoolBuilder
{
    const DEFAULT_CONTROLLER   = 'Ekyna\Bundle\AdminBundle\Controller\ResourceController';
    const CONTROLLER_INTERFACE = 'Ekyna\Bundle\AdminBundle\Controller\ResourceControllerInterface';

    const DEFAULT_OPERATOR     = 'Ekyna\Bundle\AdminBundle\Operator\ResourceOperator';
    const OPERATOR_INTERFACE   = 'Ekyna\Bundle\AdminBundle\Operator\ResourceOperatorInterface';

    const DEFAULT_REPOSITORY   = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepository';
    const REPOSITORY_INTERFACE = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\ResourceRepositoryInterface';

    const CONFIGURATION        = 'Ekyna\Bundle\AdminBundle\Pool\Configuration';
    const CLASS_METADATA       = 'Doctrine\ORM\Mapping\ClassMetadata';

    /**
     * @var ContainerBuilder
     */
    private $container;

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

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
    private $options;

    /**
     * Constructor.
     *
     * @param ContainerBuilder $container
     */
    public function __construct(ContainerBuilder $container)
    {
        $this->container = $container;
    }

    /**
     * Configures the pool builder.
     *
     * @param string $prefix
     * @param string $resourceName
     * @param array  $options
     *
     * @return PoolBuilder
     */
    public function configure($prefix, $resourceName, array $options)
    {
        $this->prefix = $prefix;
        $this->resourceName = $resourceName;
        $this->options = $this->getOptionsResolver()->resolve($options);

        return $this;
    }

    /**
     * Builds the container.
     *
     * @return PoolBuilder
     */
    public function build()
    {
        $this->createEntityClassParameter();

        $this->createConfigurationDefinition();

        $this->createManagerDefinition();
        $this->createRepositoryDefinition();
        $this->createOperatorDefinition();

        $this->createControllerDefinition();

        $this->createFormDefinition();
        $this->createTableDefinition();

        return $this;
    }

    /**
     * Returns the options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        if (null === $this->optionsResolver) {
            $this->optionsResolver = new OptionsResolver();
            $this->optionsResolver
                ->setDefaults(array(
                    'entity'     => null,
                    'repository' => self::DEFAULT_REPOSITORY,
                    'operator'   => self::DEFAULT_OPERATOR,
                    'controller' => self::DEFAULT_CONTROLLER,
                    'templates'  => null,
                    'form'       => null,
                    'table'      => null,
                    'event'      => null,
                    'parent'     => null,
                ))
                ->setAllowedTypes(array(
                    'entity'     => 'string',
                    'repository' => 'string',
                    'operator'   => 'string',
                    'controller' => 'string',
                    'templates'  => 'string',
                    'form'       => 'string',
                    'table'      => 'string',
                    'event'      => array('null', 'string'),
                    'parent'     => array('null', 'string'),
                ))
            ;
        }
        return $this->optionsResolver;
    }

    /**
     * Creates the entity class parameter.
     */
    private function createEntityClassParameter()
    {
        $id = $this->getServiceId('class');
        if ($this->container->has($id)) {
            throw new \Exception(sprintf('The parameter "%s" is reserved. Please remove his definition.', $id));
        }
        $this->container->setParameter($id, $this->options['entity']);
    }

    /**
     * Creates the Configuration service definition.
     */
    private function createConfigurationDefinition()
    {
        $id = $this->getServiceId('configuration');
        if (!$this->container->has($id)) {
            $definition = new Definition(self::CONFIGURATION);
            $definition
                ->setFactoryService('ekyna_admin.pool_factory')
                ->setFactoryMethod('createConfiguration')
                ->setArguments(array(
                    $this->prefix,
                    $this->resourceName,
                    $this->options['entity'],
                    $this->options['templates'],
                    $this->options['event'],
                    $this->options['parent']
                ))
                ->addTag('ekyna_admin.configuration', array(
                    'alias' => sprintf('%s_%s', $this->prefix, $this->resourceName))
                )
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the manager definition.
     */
    private function createManagerDefinition()
    {
        $id = $this->getServiceId('manager');
        if (!$this->container->has($id)) {
            $this->container->setAlias($id, new Alias($this->getManagerServiceId()));
        }
    }

    /**
     * Creates the Repository service definition.
     */
    private function createRepositoryDefinition()
    {
        $id = $this->getServiceId('repository');
        if (!$this->container->has($id)) {
            $class = $this->getServiceClass('repository');
            $this->checkClass($class, self::REPOSITORY_INTERFACE);
            $definition = new Definition($class);
            $definition->setArguments(array(
                new Reference($this->getServiceId('manager')),
                $this->getClassMetadataDefinition($this->options['entity'])
            ));
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the operator service definition.
     *
     * @TODO Swap with ResourceManager when ready.
     */
    private function createOperatorDefinition()
    {
        $id = $this->getServiceId('operator');
        if (!$this->container->has($id)) {
            $class = $this->getServiceClass('operator');
            $this->checkClass($class, self::OPERATOR_INTERFACE);
            $definition = new Definition($class);
            $definition->setArguments(array(
                new Reference($this->getManagerServiceId()),
                new Reference($this->getEventDispatcherServiceId()),
                new Reference($this->getServiceId('configuration')),
            ));
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Controller service definition.
     */
    private function createControllerDefinition()
    {
        $id = $this->getServiceId('controller');
        if (!$this->container->has($id)) {
            $class = $this->getServiceClass('controller');
            $this->checkClass($class, self::CONTROLLER_INTERFACE);
            $definition = new Definition($class);
            $definition
                ->addMethodCall('setConfiguration', array(new Reference($this->getServiceId('configuration'))))
                ->addMethodCall('setContainer', array(new Reference('service_container')))
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Form service definition.
     */
    private function createFormDefinition()
    {
        $id = $this->getServiceId('form_type');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('form'));
            $definition
                ->setArguments(array($this->options['entity']))
                ->addTag('form.type', array(
                    'alias' => sprintf('%s_%s', $this->prefix, $this->resourceName))
                )
            ;
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Table service definition.
     */
    private function createTableDefinition()
    {
        $id = $this->getServiceId('table_type');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('table'));
            $definition
                ->setArguments(array($this->options['entity']))
                ->addTag('table.type', array(
                    'alias' => sprintf('%s_%s', $this->prefix, $this->resourceName))
                )
            ;
            $this->container->setDefinition($id, $definition);
        }
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
        $definition = new Definition(self::CLASS_METADATA);
        $definition
            ->setFactoryService($this->getManagerServiceId())
            ->setFactoryMethod('getClassMetadata')
            ->setArguments(array($entity))
            ->setPublic(false)
        ;
        return $definition;
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
     *
     * @return string|null
     */
    private function getServiceClass($name)
    {
        $parameter = $this->getServiceId($name, '.class');
        if ($this->container->hasParameter($parameter)) {
            return $this->container->getParameter($parameter);
        }
        return $this->options[$name];
    }

    /**
     * Checks that class exists and implements the given interface.
     *
     * @param string $class
     * @param string $interface
     * @throws \RuntimeException
     */
    private function checkClass($class, $interface = null)
    {
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf('Class "%s" does not exists.', $class));
        }
        if (0 < strlen($interface)) {
            if (!in_array($interface, class_implements($class))) {
                throw new \RuntimeException(sprintf('Class "%s" must implement "%s" interface.', $class, $interface));
            }
        }
    }
}
