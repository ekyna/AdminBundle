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

        return $output;
    }

    /**
     * @param array $config
     *
     * @return array
     */
    protected function buildCss(array $config)
    {
        $inputs = array_merge(array(
            'assets/bootstrap/css/bootstrap.min.css',
            'assets/bootstrap-dialog/dist/css/bootstrap-dialog.min.css',
            'assets/jquery-ui/themes/base/jquery-ui.min.css',
            'assets/jquery-ui/themes/smoothness/jquery-ui.min.css',

            '@fontawesome_css',
            '@form_css',

            '@EkynaAdminBundle/Resources/asset/css/bootstrap.overrides.css',
            '@EkynaAdminBundle/Resources/asset/css/jquery-ui.overrides.css',
            '@EkynaAdminBundle/Resources/asset/css/layout.css',
            '@EkynaAdminBundle/Resources/asset/css/elements.css',
            '@EkynaAdminBundle/Resources/asset/css/ui-elements.css',
            '@EkynaAdminBundle/Resources/asset/css/show.css',
        ), $config['css_inputs']);

        return array(
            'inputs'  => $inputs,
            'filters' => array('yui_css', 'cssrewrite'), // 'cssrewrite'
            'output'  => $config['output_dir'].'css/admin-main.css',
            'debug'   => false,
        );
    }
}
