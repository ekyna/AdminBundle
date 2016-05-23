<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Ekyna\Bundle\CoreBundle\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EkynaAdminExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminExtension extends Extension
{
    /**
     * {@inheritDoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $loader = new Loader\XmlFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.xml');

        $this->configureResources($config['resources'], $container);
        $this->configureMenus($config['menus'], $container);

        $container->setParameter('ekyna_admin.config.dashboard', $config['dashboard']);
        $container->setParameter('ekyna_admin.config.front', [
            'logo_path'   => $config['logo_path'],
            'stylesheets' => $config['stylesheets']
        ]);

        if (!$container->hasParameter('ekyna_admin.translation_mapping')) {
            $container->setParameter('ekyna_admin.translation_mapping', []);
        }
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
        foreach ($resources as $prefix => $config) {
            foreach ($config as $resourceName => $parameters) {
                $builder
                    ->configure($prefix, $resourceName, $parameters)
                    ->build();
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

        foreach ($menus as $groupName => $groupConfig) {
            $pool->addMethodCall('createGroup', [[
                'name' => $groupName,
                'label' => $groupConfig['label'],
                'icon' => $groupConfig['icon'],
                'position' => $groupConfig['position'],
                'domain' => $groupConfig['domain'],
                'route' => $groupConfig['route'],
            ]]);
            foreach ($groupConfig['entries'] as $entryName => $entryConfig) {
                $pool->addMethodCall('createEntry', [$groupName, [
                    'name' => $entryName,
                    'route' => $entryConfig['route'],
                    'label' => $entryConfig['label'],
                    'resource' => $entryConfig['resource'],
                    'position' => $entryConfig['position'],
                    'domain' => $entryConfig['domain'],
                ]]);
            }
        }
    }
}
