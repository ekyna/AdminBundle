<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;

/**
 * AbstractExtension.
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExtension extends Extension
{
    protected $configDirectory = '/../Resources/config';
    protected $configFiles = array(
        'services',
    );

    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        throw new \Exception('AbstractExtension:load() has to be overridden.');
    }

    /**
     * Configures the pool builder.
     * 
     * @param array                  $configs
     * @param string                 $prefix
     * @param ConfigurationInterface $configuration
     * @param ContainerBuilder       $container
     *
     * @return array(array, \Symfony\Component\DependencyInjection\Loader\FileLoader)
     */
    public function configure(array $configs, $prefix, ConfigurationInterface $configuration, ContainerBuilder $container)
    {
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new XmlFileLoader($container, new FileLocator($this->getConfigurationDirectory()));
        $this->loadConfigurationFile($this->configFiles, $loader);

        if (array_key_exists('pools', $config)) {
            $builder = new PoolBuilder($container);
            foreach ($config['pools'] as $resourceName => $params) {
                $builder
                    ->configure($prefix, $resourceName, $params)
                    ->build()
                ;
            }
        }

        return array($config, $loader);
    }

    /**
     * Loads bundle configuration files.
     * 
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
     * Returns the configuration directory.
     *
     * @return string
     * 
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
