<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * ResourceExtension
 */
abstract class AbstractExtension extends Extension
{
    protected $prefix = 'ekyna';
    protected $configDirectory = '/../Resources/config';
    protected $configFiles = array(
        'services',
    );

    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        //$this->configure($configs, new Configuration(), $container);
        throw new \Exception('AbstractExtension:load() has to be overridden.');
    }

    /**
     * @param array                  $configs
     * @param ConfigurationInterface $configuration
     * @param ContainerBuilder       $container
     *
     * @return array
     */
    public function configure(array $configs, ConfigurationInterface $configuration, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator($this->getConfigurationDirectory()));
        $this->loadConfigurationFile($this->configFiles, $loader);

        foreach ($config['pools'] as $resourceName => $params) {
            $builder = new PoolBuilder($container, $this->prefix, $resourceName, $params);
            $builder->build();
        }

        return array($config, $loader);
    }

    /**
     * @param array         $config
     * @param XmlFileLoader $loader
     */
    protected function loadConfigurationFile(array $config, XmlFileLoader $loader)
    {
        foreach ($config as $filename) {
            if (file_exists($file = sprintf('%s/%s.xml', $this->getConfigurationDirectory(), $filename))) {
                $loader->load($file);
            }
        }
    }

    /**
     * Get the configuration directory
     *
     * @return string
     * @throws \Exception
     */
    protected function getConfigurationDirectory()
    {
        $reflector = new \ReflectionClass($this);
        $fileName = $reflector->getFileName();
    
        if (!is_dir($directory = dirname($fileName) . $this->configDirectory)) {
            throw new \Exception(sprintf('The configuration directory "%s" does not exists.', $directory));
        }
    
        return $directory;
    }
}
