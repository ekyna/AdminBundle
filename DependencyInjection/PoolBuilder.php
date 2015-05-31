<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Alias;
use Symfony\Component\OptionsResolver\Exception\InvalidOptionsException;
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

    const TRANSLATABLE_DEFAULT_REPOSITORY   = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\TranslatableResourceRepository';
    const TRANSLATABLE_REPOSITORY_INTERFACE = 'Ekyna\Bundle\AdminBundle\Doctrine\ORM\TranslatableResourceRepositoryInterface';

    const DEFAULT_TEMPLATES    = 'EkynaAdminBundle:Entity/Default';

    const FORM_INTERFACE       = 'Symfony\Component\Form\FormTypeInterface';
    const TABLE_INTERFACE      = 'Ekyna\Component\Table\TableTypeInterface';
    const EVENT_INTERFACE      = 'Ekyna\Bundle\AdminBundle\Event\ResourceEventInterface';

    const CONFIGURATION        = 'Ekyna\Bundle\AdminBundle\Pool\Configuration';
    const CLASS_METADATA       = 'Doctrine\ORM\Mapping\ClassMetadata';

    /**
     * @var OptionsResolver
     */
    static private $optionsResolver;

    /**
     * The required templates (name => extensions[])[].
     * @var array
     */
    static private $templates = array(
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

        // TODO search repository service

        $this->createControllerDefinition();

        $this->createFormDefinition();
        $this->createTableDefinition();

        $this->configureTranslations();

        return $this;
    }

    /**
     * Returns the options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        if (null === self::$optionsResolver) {
            $classExists = function ($class) {
                if (!class_exists($class)) {
                    throw new InvalidOptionsException(sprintf('Class %s does not exists.', $class));
                }
                return true;
            };
            $classExistsAndImplements = function($class, $interface) use ($classExists) {
                $classExists($class);
                if (!in_array($interface, class_implements($class))) {
                    throw new InvalidOptionsException(sprintf('Class %s must implement %s.', $class, $interface));
                }
                return true;
            };
            $validOperator = function ($class) use ($classExistsAndImplements) {
                return $classExistsAndImplements($class, self::OPERATOR_INTERFACE);
            };
            $validController = function ($class) use ($classExistsAndImplements) {
                return $classExistsAndImplements($class, self::CONTROLLER_INTERFACE);
            };
            $validForm = function ($class) use ($classExistsAndImplements) {
                return $classExistsAndImplements($class, self::FORM_INTERFACE);
            };
            $validTable = function ($class) use ($classExistsAndImplements) {
                return $classExistsAndImplements($class, self::TABLE_INTERFACE);
            };
            $validEvent = function ($class) use ($classExistsAndImplements) {
                if (null === $class) {
                    return true;
                }
                return $classExistsAndImplements($class, self::EVENT_INTERFACE);
            };

            self::$optionsResolver = new OptionsResolver();
            self::$optionsResolver
                ->setDefaults(array(
                    'entity'      => null,
                    'repository'  => null,
                    'operator'    => self::DEFAULT_OPERATOR,
                    'controller'  => self::DEFAULT_CONTROLLER,
                    'templates'   => null,
                    'form'        => null,
                    'table'       => null,
                    'event'       => null,
                    'parent'      => null,
                    'translation' => null,
                ))
                ->setAllowedTypes(array(
                    'entity'      => 'string',
                    'repository'  => 'string',
                    'operator'    => 'string',
                    'controller'  => 'string',
                    'templates'   => array('null', 'string', 'array'),
                    'form'        => 'string',
                    'table'       => 'string',
                    'event'       => array('null', 'string'),
                    'parent'      => array('null', 'string'),
                    'translation' => array('null', 'array'),
                ))
                ->setAllowedValues(array(
                    'entity'      => $classExists,
                    'operator'    => $validOperator,
                    'controller'  => $validController,
                    'form'        => $validForm,
                    'table'       => $validTable,
                    'event'       => $validEvent,
                ))
                ->setNormalizers(array(
                    'repository' => function($options, $value) use ($classExistsAndImplements) {
                        $translatable = is_array($options['translation']);
                        $interface = $translatable ? self::TRANSLATABLE_REPOSITORY_INTERFACE : self::REPOSITORY_INTERFACE;
                        if (null === $value) {
                            if ($translatable) {
                                $value = self::TRANSLATABLE_DEFAULT_REPOSITORY;
                            } else {
                                $value = self::DEFAULT_REPOSITORY;
                            }
                        }
                        $classExistsAndImplements($value, $interface);
                        return $value;
                    },
                    'translation' => function ($options, $value) use ($classExistsAndImplements) {
                        if (is_array($value)) {
                            if (!array_key_exists('entity', $value)) {
                                throw new InvalidOptionsException('translation.entity must be defined.');
                            }
                            if (!array_key_exists('fields', $value)) {
                                throw new InvalidOptionsException('translation.fields must be defined.');
                            }
                            if (!is_array($value['fields']) || empty($value['fields'])) {
                                throw new InvalidOptionsException('translation.fields can\'t be empty.');
                            }
                            if (!array_key_exists('repository', $value)) {
                                $value['repository'] = self::DEFAULT_REPOSITORY;
                            }
                            $classExistsAndImplements($value['repository'], self::REPOSITORY_INTERFACE);
                        }
                        return $value;
                    },
                    // TODO templates normalization ?
                ))
            ;
        }
        return self::$optionsResolver;
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

        $this->configureInheritanceMapping(
            $this->prefix.'.'.$this->resourceName,
            $this->options['entity'],
            $this->options['repository']
        );
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
        foreach (self::$templates as $name => $extensions) {
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
        $id = $this->getServiceId('repository');
        if (!$this->container->has($id)) {
            $definition = new Definition($class = $this->getServiceClass('repository'));
            $definition->setArguments(array(
                new Reference($this->getServiceId('manager')),
                new Reference($this->getServiceId('metadata'))
            ));
            if (is_array($this->options['translation'])) {
                $definition
                    ->addMethodCall('setLocaleProvider', array(new Reference('ekyna_admin.locale_provider.request'))) // TODO configurable ?
                    ->addMethodCall('setTranslatableFields', array($this->options['translation']['fields']))
                ;
            }
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
            $definition = new Definition($this->getServiceClass('operator'));
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
        $id = $this->getServiceId('controller');
        if (!$this->container->has($id)) {
            $definition = new Definition($this->getServiceClass('controller'));
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
     * Configure the translation
     */
    private function configureTranslations()
    {
        if (null !== array_key_exists('translation', $this->options) && is_array($this->options['translation'])) {
            $translatable = $this->options['entity'];
            $translation = $this->options['translation']['entity'];

            $id = sprintf('%s.%s_translation', $this->prefix, $this->resourceName);

            // Load metadata event mapping
            $mapping = array(
                $translatable => $translation,
                $translation  => $translatable,
            );
            if ($this->container->hasParameter('ekyna_admin.translation_mapping')) {
                $mapping = array_merge($this->container->getParameter('ekyna_admin.translation_mapping'), $mapping);
            }
            $this->container->setParameter('ekyna_admin.translation_mapping', $mapping);

            // Translation class parameter
            if (!$this->container->hasParameter($id.'.class')) {
                $this->container->setParameter($id.'.class', $translation);
            }

            // Inheritance mapping
            $this->configureInheritanceMapping($id, $translation, $this->options['translation']['repository']);
        }
    }

    /**
     * Configures mapping inheritance.
     *
     * @param string $id
     * @param string $entity
     * @param string $repository
     */
    private function configureInheritanceMapping($id, $entity, $repository)
    {
        $entities = array(
            $id => array(
                'class'      => $entity,
                'repository' => $repository,
            ),
        );

        if ($this->container->hasParameter('ekyna_core.entities')) {
            $entities = array_merge($this->container->getParameter('ekyna_core.entities'), $entities);
        }
        $this->container->setParameter('ekyna_core.entities', $entities);
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
     *
     * @return string
     */
    private function getServiceId($name)
    {
        return sprintf('%s.%s.%s', $this->prefix, $this->resourceName, $name);
    }

    /**
     * Returns the service class for the given name.
     *
     * @param string $name
     *
     * @throws \RuntimeException
     *
     * @return string|null
     */
    private function getServiceClass($name)
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
        return $class;
    }
}
