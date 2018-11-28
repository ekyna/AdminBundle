<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Ekyna\Bundle\ResourceBundle\DependencyInjection\AbstractExtension;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Class EkynaAdminExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminExtension extends AbstractExtension
{
    /**
     * @inheritdoc
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $config = $this->configure($configs, 'ekyna_admin', new Configuration(), $container);

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

        $container
            ->getDefinition('ekyna_admin.security.event_subscriber')
            ->replaceArgument(3, $config['notification']);
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
