<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;

/**
 * Class EkynaAdminExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminExtension extends Extension implements PrependExtensionInterface
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.xml');

        $container->setParameter('ekyna_admin.logo_path', $config['logo_path']);

        $this->configureResources($config['resources'], $container);
        $this->configureMenus($config['menus'], $container);
    }

    /**
     * Configures the resources.
     *
     * @param array $resources
     * @param ContainerBuilder $container
     */
    private function configureResources(array $resources, ContainerBuilder $container)
    {
        $builder = new PoolBuilder($container);
        foreach($resources as $prefix => $config) {
            foreach($config as $resourceName => $parameters) {
                $builder
                    ->configure($prefix, $resourceName, $parameters)
                    ->build()
                ;
            }
        }
    }

    /**
     * Configures the menus.
     *
     * @param array $menus
     * @param ContainerBuilder $container
     */
    private function configureMenus(array $menus, ContainerBuilder $container)
    {
        if (!$container->hasDefinition('ekyna_admin.menu.pool')) {
            return;
        }

        $pool = $container->getDefinition('ekyna_admin.menu.pool');

        foreach($menus as $groupName => $groupConfig) {
            $pool->addMethodCall('createGroup', array(array(
                'name'     => $groupName,
                'label'    => $groupConfig['label'],
                'icon'     => $groupConfig['icon'],
                'position' => $groupConfig['position'],
            )));
            foreach($groupConfig['entries'] as $entryName => $entryConfig) {
                $pool->addMethodCall('createEntry', array('content', array(
                    'name'     => $entryName,
                    'route'    => $entryConfig['route'],
                    'label'    => $entryConfig['label'],
                    'resource' => $entryConfig['resource'],
                    'position' => $entryConfig['position'],
                    'domain'   => $entryConfig['domain'],
                )));
            }
        }
    }

    /**
     * {@inheritDoc}
     */
    public function prepend(ContainerBuilder $container)
    {
        $bundles = $container->getParameter('kernel.bundles');
        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        if (array_key_exists('TwigBundle', $bundles)) {
            $this->configureTwigBundle($container);
        }
        if (array_key_exists('AsseticBundle', $bundles)) {
            $this->configureAsseticBundle($container, $config);
        }
    }

    /**
     * Configures the TwigBundle.
     *
     * @param ContainerBuilder $container
     */
    private function configureTwigBundle(ContainerBuilder $container)
    {
        $container->prependExtensionConfig('twig', array(
            'form' => array('resources' => array('EkynaAdminBundle:Form:form_div_layout.html.twig')),
        ));
    }

    /**
     * Configures the AsseticBundle.
     *
     * @param ContainerBuilder $container
     * @param array            $config
     */
    private function configureAsseticBundle(ContainerBuilder $container, array $config)
    {
        $asseticConfig = new AsseticConfiguration;
        $container->prependExtensionConfig('assetic', array(
            'bundles' => array('EkynaAdminBundle'),
            'assets' => $asseticConfig->build($config),
        ));
    }
}
