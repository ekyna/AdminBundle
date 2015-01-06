<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\Config\Definition\ConfigurationInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\XmlFileLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * Class AbstractExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
abstract class AbstractExtension extends Extension implements PrependExtensionInterface
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
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        if (is_dir($dir = $this->getConfigurationDirectory().'/prepend')) {
            $bundles = $container->getParameter('kernel.bundles');
            $finder = new Finder();

            /** @var \Symfony\Component\Finder\SplFileInfo $file */
            foreach ($finder->in($this->getConfigurationDirectory() . '/prepend')->files()->name('*.yml') as $file) {
                $bundle = $file->getBasename('.yml');
                if (array_key_exists($bundle, $bundles)) {
                    $configs = Yaml::parse($file->getRealPath());
                    foreach ($configs as $key => $config) {
                        $container->prependExtensionConfig($key, $config);
                    }
                }
            }
        }
    }

    /**
     * Configures the pool builder and returns the bundle processed configuration.
     * 
     * @param array                  $configs
     * @param string                 $prefix
     * @param ConfigurationInterface $configuration
     * @param ContainerBuilder       $container
     *
     * @return array
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

        return $config;
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
     * @throws \Exception
     */
    protected function getConfigurationDirectory()
    {
        $reflector = new \ReflectionClass($this);
        $fileName = $reflector->getFileName();
    
        if (!is_dir($directory = realpath(dirname($fileName) . $this->configDirectory))) {
            throw new \Exception(sprintf('The configuration directory "%s" does not exists.', $directory));
        }
    
        return $directory;
    }
}
