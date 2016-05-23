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
    /**
     * @var OptionsResolver
     */
    private $optionsResolver;

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
            $resolver->setAllowedTypes('templates', 'array');

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
}
