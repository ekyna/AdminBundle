<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

/**
 * Class AsseticConfiguration
 * @package Ekyna\Bundle\AdminBundle\DependencyInjection
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AsseticConfiguration
{
    /**
     * Builds the assetic configuration.
     *
     * @param array $config
     *
     * @return array
     */
    public function build(array $config)
    {
        $output = array();

        // Fix path in output dir
        if ('/' !== substr($config['output_dir'], -1) && strlen($config['output_dir']) > 0) {
            $config['output_dir'] .= '/';
        }

        $output['admin_css'] = $this->buildCss($config);
        $output['admin_js'] = $this->buildJs($config);

        return $output;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function buildCss(array $config)
    {
        $inputs = array(
            '%kernel.root_dir%/../vendor/twbs/bootstrap/dist/css/bootstrap.min.css',
            '@fontawesome_css',
            '@core_css',

            '@EkynaAdminBundle/Resources/asset/css/lib/jquery-ui.css',
            '@EkynaAdminBundle/Resources/asset/css/bootstrap-overrides.css',
            '@EkynaAdminBundle/Resources/asset/css/layout.css',
            '@EkynaAdminBundle/Resources/asset/css/elements.css',
            '@EkynaAdminBundle/Resources/asset/css/ui-elements.css',
            '@EkynaAdminBundle/Resources/asset/css/show.css',

            '@EkynaTableBundle/Resources/asset/css/table.css',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('yui_css'), // 'cssrewrite'
            'output'  => $config['output_dir'].'css/admin.css',
            'debug'   => false,
        );
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function buildJs(array $config)
    {        
        $inputs = array(
            '@jquery',
            '@bootstrap_js',
            '@core_js',

            '@EkynaAdminBundle/Resources/asset/js/jquery-ui.js',
            '@EkynaAdminBundle/Resources/asset/js/theme.js',
            '@EkynaAdminBundle/Resources/asset/js/ui.js',

            '@EkynaTableBundle/Resources/asset/js/table.js',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('yui_js'),
            'output'  => $config['output_dir'].'js/admin.js',
            'debug'   => false,
        );
    }
}
