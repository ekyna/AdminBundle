<?php

declare(strict_types=1);

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

use Ekyna\Bundle\AdminBundle\Model\UserInterface;
use Ekyna\Bundle\ResourceBundle\DependencyInjection\PrependBundleConfigTrait;
use Ekyna\Component\User\Service\OAuth\OAuthConfigurator;
use Ekyna\Component\User\Service\SecurityConfigurator;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use KnpU\OAuth2ClientBundle\DependencyInjection\Configuration as KnpuConfiguration;

use function array_keys;
use function array_unique;
use function array_unshift;
use function trim;
use function usort;

/**
 * Class EkynaAdminExtension
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author  Étienne Dauvergne <contact@ekyna.com>
 */
class EkynaAdminExtension extends Extension implements PrependExtensionInterface
{
    use PrependBundleConfigTrait;

    public function prepend(ContainerBuilder $container): void
    {
        $this->prependBundleConfigFiles($container);

        $configs = $container->getExtensionConfig($this->getAlias());
        $config = $this->processConfiguration(new Configuration(), $configs);

        $this->prependSecurity($config, $container);
    }

    public function load(array $configs, ContainerBuilder $container): void
    {
        $config = $this->processConfiguration(new Configuration(), $configs);

        // Routing prefix
        $container->setParameter('ekyna_admin.routing_prefix', '/' . trim($config['routing_prefix'], '/'));

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services/action.php');
        $loader->load('services/console.php');
        $loader->load('services/controller.php');
        $loader->load('services/dashboard.php');
        $loader->load('services/form.php');
        $loader->load('services/menu.php');
        $loader->load('services/security.php');
        $loader->load('services/show.php');
        $loader->load('services/table.php');
        $loader->load('services/twig.php');
        $loader->load('services.php');

        if (in_array($container->getParameter('kernel.environment'), ['dev', 'test'], true)) {
            $loader->load('services/dev.php');
        }

        $this->configureDashboard($config['dashboard'], $container);
        $this->configureMenus($config['menus'], $container);
        $this->configureSecurity($config['security'], $container);
        $this->configureShow($config['show'], $container);
        $this->configureUi($config, $container);
    }

    private function configureDashboard(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_admin.dashboard')
            ->replaceArgument(0, $config);
    }

    private function configureMenus(array $menus, ContainerBuilder $container): void
    {
        $pool = $container->getDefinition('ekyna_admin.menu.pool');

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
                        'label'    => $entryConfig['label'],
                        'domain'   => $entryConfig['domain'],
                        'position' => $entryConfig['position'],
                        'route'    => $entryConfig['route'],
                        'resource' => $entryConfig['resource'],
                        'action'   => $entryConfig['action'],
                    ],
                ]);
            }
        }
    }

    private function configureSecurity(array $config, ContainerBuilder $container): void
    {
        $container
            ->getDefinition('ekyna_admin.controller.security')
            ->replaceArgument(2, [
                'template'    => '@EkynaAdmin/Security/login.html.twig',
                'remember_me' => $config['remember_me'],
                'target_path' => 'admin_dashboard',
            ]);

        $container
            ->getDefinition('ekyna_admin.listener.security')
            ->replaceArgument(3, $config['notification']);
    }

    private function configureShow(array $config, ContainerBuilder $container): void
    {
        $templates = $config['templates'];
        array_unshift($templates, $config['default_template']);

        $container
            ->getDefinition('ekyna_admin.show.type_registry')
            ->addMethodCall('registerTemplates', [array_unique($templates)]);
    }

    private function configureUi(array $config, ContainerBuilder $container): void
    {
        $buttons = $config['navbar']['buttons'];
        usort($buttons, function ($a, $b) {
            if ($a['position'] == $b['position']) {
                return 0;
            }

            return $a['position'] > $b['position'] ? 1 : -1;
        });
        $config['navbar']['buttons'] = $buttons;

        $container
            ->getDefinition('ekyna_admin.renderer.ui')
            ->replaceArgument(4, [
                'stylesheets' => $config['stylesheets'],
                'navbar'      => $config['navbar'],
            ]);
    }

    private function prependSecurity(array $config, ContainerBuilder $container): void
    {
        $routingPrefix = '/' . trim($config['routing_prefix'], '/');

        $configs = $container->getExtensionConfig('knpu_oauth2_client');
        $knpuConfig = $this->processConfiguration(new KnpuConfiguration(), $configs);

        $customAuthenticators = ['ekyna_admin.security.authenticator.form_login'];

        // OAuth authenticators
        $owners = array_keys($knpuConfig['clients']);
        foreach (array_keys(OAuthConfigurator::OWNERS) as $owner) {
            if (in_array('ekyna_admin_' . $owner, $owners, true)) {
                $customAuthenticators[] = OAuthConfigurator::authenticator('ekyna_admin', $owner);
            }
        }

        $configurator = new SecurityConfigurator();
        $configurator->configure($container, [
            'role_hierarchy'   => [
                'ROLE_ADMIN'       => 'ROLE_USER',
                'ROLE_SUPER_ADMIN' => 'ROLE_ADMIN',
            ],
            'providers'        => [
                'ekyna_admin' => [
                    'id' => 'ekyna_admin.provider.user',
                ],
            ],
            'password_hashers' => [
                UserInterface::class => [
                    'algorithm' => 'argon2i',
                ],
            ],
            'firewalls'        => [
                'admin' => [
                    '_priority'             => 1024,
                    'pattern'               => "^$routingPrefix",
                    'provider'              => 'ekyna_admin',
                    'custom_authenticators' => $customAuthenticators,
                    'login_throttling'      => [
                        'max_attempts' => 3,
                        'interval'     => '15 minutes',
                    ],
                    'remember_me'           => [
                        'secret'                => '%kernel.secret%',
                        'name'                  => 'ADMIN_REMEMBER_ME',
                        'path'                  => $routingPrefix,
                        'lifetime'              => 60 * 60 * 24 * 7,
                        'remember_me_parameter' => $config['security']['remember_me'],
                    ],
                    'logout'                => [
                        'path'               => 'admin_security_logout',
                        'target'             => 'admin_security_login',
                        'invalidate_session' => false,
                    ],
                ],
            ],
            'access_control'   => [
                [
                    'path' => "^$routingPrefix/(login|oauth)",
                    'role' => 'PUBLIC_ACCESS',
                ],
                [
                    'path' => "^$routingPrefix",
                    'role' => 'ROLE_ADMIN',
                ],
            ],
        ]);
    }
}
