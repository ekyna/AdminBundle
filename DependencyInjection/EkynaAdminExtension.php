<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\Dashboard\Dashboard;
use Ekyna\Bundle\AdminBundle\EventListener\SecurityEventSubscriber;
use Ekyna\Bundle\AdminBundle\Menu\MenuPool;
use Ekyna\Bundle\AdminBundle\Service\Renderer\AdminRenderer;
use Ekyna\Bundle\AdminBundle\Show\Registry;
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

        $buttons = $config['navbar']['buttons'];
        usort($buttons, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return $a['position'] > $b['position'] ? 1 : -1;
        });
        $config['navbar']['buttons'] = $buttons;

        $container
            ->getDefinition(AdminRenderer::class)
            ->replaceArgument(4, [
                'stylesheets' => $config['stylesheets'],
                'navbar'      => $config['navbar'],
            ]);

        $container
            ->getDefinition(Dashboard::class)
            ->replaceArgument(0, $config['dashboard']);

        $templates = $config['show']['templates'];
        array_unshift($templates, $config['show']['default_template']);

        $container
            ->getDefinition(Registry::class)
            ->addMethodCall('registerTemplates', [$templates]);

        $container
            ->getDefinition(SecurityEventSubscriber::class)
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
        if (!$container->hasDefinition(MenuPool::class)) {
            return;
        }

        $pool = $container->getDefinition(MenuPool::class);

        foreach ($menus as $groupName => $groupConfig) {
            $pool->addMethodCall('createGroup', [
                [
                    'name'     => $groupName,
                    'label'    => $groupConfig['label'],
                    'icon'     => $groupConfig['icon'],
                    'position' => $groupConfig['position'],
                    'domain'   => $groupConfig['domain'],
                    'route'    => $groupConfig['route'],
                ],
            ]);
            foreach ($groupConfig['entries'] as $entryName => $entryConfig) {
                $pool->addMethodCall('createEntry', [
                    $groupName,
                    [
                        'name'     => $entryName,
                        'route'    => $entryConfig['route'],
                        'label'    => $entryConfig['label'],
                        'resource' => $entryConfig['resource'],
                        'position' => $entryConfig['position'],
                        'domain'   => $entryConfig['domain'],
                    ],
                ]);
            }
        }
    }
}
