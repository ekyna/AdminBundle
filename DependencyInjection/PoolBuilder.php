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

    const DEFAULT_TEMPLATES    = 'EkynaAdminBundle:Entity/Default';

    const FORM_INTERFACE       = 'Symfony\Component\Form\FormTypeInterface';
    const TABLE_INTERFACE      = 'Ekyna\Component\Table\TableTypeInterface';

    const CONFIGURATION        = 'Ekyna\Bundle\AdminBundle\Pool\Configuration';
    const CLASS_METADATA       = 'Doctrine\ORM\Mapping\ClassMetadata';

    /**
     * The required templates (name => extensions[])[].
     * @var array
     */
    private $templates = array(
        '_form'  => array('html'),
        'list'   => array('html', 'xml'),
        'new'    => array('html', 'xml'),
        'show'   => array('html'),
        'edit'   => array('html'),
        'remove' => array('html'),
    );

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

        $this->createMetadataDefinition();
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
                    'templates'  => array('null', 'string', 'array'),
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
        if (!$this->container->hasParameter($id)) {
            $this->container->setParameter($id, $this->options['entity']);
        }

        $coreEntities = array(
            $this->prefix.'.'.$this->resourceName => array(
                'class'      => $this->options['entity'],
                'repository' => $this->options['repository'],
            ),
        );

        if ($this->container->hasParameter('ekyna_core.entities')) {
            $coreEntities = array_merge($this->container->getParameter('ekyna_core.entities'), $coreEntities);
        }
        $this->container->setParameter('ekyna_core.entities', $coreEntities);
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
                    $this->buildTemplateList($this->options['templates']),
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
     * Builds the templates list.
     *
     * @param mixed $templatesConfig
     * @return array
     */
    private function buildTemplateList($templatesConfig)
    {
        $templateNamespace = self::DEFAULT_TEMPLATES;
        if (is_string($templatesConfig)) {
            $templateNamespace = $templatesConfig;
        }
        $templatesList = [];
        foreach ($this->templates as $name => $extensions) {
            foreach ($extensions as $extension) {
                $file = $name.'.'.$extension;
                $templatesList[$file] = $templateNamespace.':'.$file;
            }
        }
        // TODO add resources controller traits templates ? (like new_child.html)
        if (is_array($templatesConfig)) {
            $templatesList = array_merge($templatesList, $templatesConfig);
        }
        return $templatesList;
    }

    /**
     * Creates the Table service definition.
     */
    private function createMetadataDefinition()
    {
        $id = $this->getServiceId('metadata');
        if (!$this->container->has($id)) {
            $definition = new Definition(self::CLASS_METADATA);
            $definition
                ->setFactoryService($this->getManagerServiceId())
                ->setFactoryMethod('getClassMetadata')
                ->setArguments(array(
                    $this->container->getParameter($this->getServiceId('class'))
                ))//->setPublic(false)
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
        // TODO repository class parameter (check, set, use)
        $id = $this->getServiceId('repository');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('repository', self::REPOSITORY_INTERFACE));
            $definition->setArguments(array(
                new Reference($this->getServiceId('manager')),
                new Reference($this->getServiceId('metadata'))
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
        // TODO operator class parameter (check, set, use)
        $id = $this->getServiceId('operator');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('operator', self::OPERATOR_INTERFACE));
            $definition->setArguments(array(
                new Reference($this->getManagerServiceId()),
                new Reference($this->getEventDispatcherServiceId()),
                new Reference($this->getServiceId('configuration')),
                $this->container->getParameter('kernel.debug')
            ));
            $this->container->setDefinition($id, $definition);
        }
    }

    /**
     * Creates the Controller service definition.
     */
    private function createControllerDefinition()
    {
        // TODO controller class parameter (check, set, use)
        $id = $this->getServiceId('controller');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('controller', self::CONTROLLER_INTERFACE));
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
        // TODO form_type class parameter (check, set, use)
        $id = $this->getServiceId('form_type');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('form', self::FORM_INTERFACE));
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
        // TODO table type class parameter (check, set, use)
        $id = $this->getServiceId('table_type');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('table', self::TABLE_INTERFACE));
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
     * @param string $interface
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    private function getServiceClass($name, $interface = null)
    {
        $serviceId = $this->getServiceId($name);
        $parameterId = $serviceId.'.class';
        if ($this->container->hasParameter($parameterId)) {
            $class = $this->container->getParameter($parameterId);
        } elseif (array_key_exists($name, $this->options)) {
            $class = $this->options[$name];
        } else {
            throw new \RuntimeException(sprintf('Undefined "%s" service class.', $name));
        }
        if (!class_exists($class)) {
            throw new \RuntimeException(sprintf(
                '%s service (%s) class "%s" does not exists.',
                ucfirst($name),
                $serviceId,
                $class
            ));
        }
        if (0 < strlen($interface)) {
            if (!in_array($interface, class_implements($class))) {
                throw new \RuntimeException(sprintf(
                    '%s service (%s) class "%s" must implement "%s" interface.',
                    ucfirst($name),
                    $serviceId,
                    $class,
                    $interface
                ));
            }
        }
        return $class;
    }
}
