<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Ekyna\Bundle\CoreBundle\DependencyInjection\Extension;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader;

/**
 * Class EkynaAdminExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
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

        $this->configureMenus($config['menus'], $container);

        $navbarConfig = $config['navbar'];
        usort($navbarConfig['buttons'], function($a, $b) {
            if ($a['position'] == $b['position']) return 0;
            return $a['position'] > $b['position'] ? 1 : -1;
        });

        $container->setParameter('ekyna_admin.config.dashboard', $config['dashboard']);
        $container->setParameter('ekyna_admin.config.front', [
            'logo_path'   => $config['logo_path'],
            'stylesheets' => $config['stylesheets'],
            'navbar'      => $config['navbar'],
        ]);

        $templates = $config['show']['templates'];
        array_unshift($templates, $config['show']['default_template']);

        $container
            ->getDefinition('ekyna_admin.show.registry')
            ->addMethodCall('registerTemplates', [$templates]);
    }

    /**
     * Configures the menus.
     *
     * @param array            $menus
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
                'name'     => $groupName,
                'label'    => $groupConfig['label'],
                'icon'     => $groupConfig['icon'],
                'position' => $groupConfig['position'],
                'domain'   => $groupConfig['domain'],
                'route'    => $groupConfig['route'],
            ]]);
            foreach ($groupConfig['entries'] as $entryName => $entryConfig) {
                $pool->addMethodCall('createEntry', [$groupName, [
                    'name'     => $entryName,
                    'route'    => $entryConfig['route'],
                    'label'    => $entryConfig['label'],
                    'resource' => $entryConfig['resource'],
                    'position' => $entryConfig['position'],
                    'domain'   => $entryConfig['domain'],
                ]]);
            }
        }
    }
}
