<?php

namespace Ekyna\Bundle\AdminBundle\Pool;

use Doctrine\Common\Inflector\Inflector;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * Class ConfigurationFactory
 * @package Ekyna\Bundle\AdminBundle\Pool
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class ConfigurationFactory
{
    // TODO sync with PoolBuilder
    const DEFAULT_TEMPLATES = 'EkynaAdminBundle:Entity/Default';

    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

    /**
     * The required templates (name => extensions[])[].
     * @var array
     */
    static private $templates = [
        '_form'  => ['html'],
        'list'   => ['html', 'xml'],
        'new'    => ['html', 'xml'],
        'show'   => ['html'],
        'edit'   => ['html'],
        'remove' => ['html'],
    ];

    /**
     * Creates and register a configuration
     *
     * @param array $config
     *
     * @return \Ekyna\Bundle\AdminBundle\Pool\Configuration
     */
    public function createConfiguration(array $config)
    {
        return new Configuration($this->getOptionsResolver()->resolve($config));
    }

    /**
     * Returns the config options resolver.
     *
     * @return OptionsResolver
     */
    private function getOptionsResolver()
    {
        if (!$this->optionsResolver) {
            $resolver = new OptionsResolver();

            $resolver->setRequired(['namespace', 'id', 'classes', 'templates']);

            $resolver->setDefault('parent_id', null);
            $resolver->setDefault('name', function (Options $options) {
                return Inflector::camelize($options['id']);
            });

            $resolver->setAllowedTypes('namespace', 'string');
            $resolver->setAllowedTypes('id', 'string');
            $resolver->setAllowedTypes('name', 'string');
            $resolver->setAllowedTypes('parent_id', ['null', 'string']);
            $resolver->setAllowedTypes('classes', 'array');
            $resolver->setAllowedTypes('templates', ['null', 'string', 'array']);

            $resolver->setAllowedValues('classes', function ($value) {
                if (!empty(array_diff(array_keys($value), ['resource', 'form_type', 'event']))) {
                    return false;
                }
                foreach ($value as $class) {
                    if ($class && !class_exists($class)) {
                        return false;
                    }
                }

                return true;
            });

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('templates', function (Options $options, $value) {
                return $this->buildTemplateList($value);
            });

            $classesResolver = new OptionsResolver();

            $classesResolver->setRequired(['resource', 'form_type']);

            $classesResolver->setDefault('event', null);

            $classesResolver->setAllowedTypes('resource', 'string');
            $classesResolver->setAllowedTypes('form_type', 'string');
            $classesResolver->setAllowedTypes('event', ['null', 'string']);

            /** @noinspection PhpUnusedParameterInspection */
            $resolver->setNormalizer('classes', function (Options $options, $value) use ($classesResolver) {
                return $classesResolver->resolve($value);
            });

            $this->optionsResolver = $resolver;
        }

        return $this->optionsResolver;
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
}
