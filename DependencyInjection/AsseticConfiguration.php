<?php

namespace Ekyna\Bundle\AdminBundle\DependencyInjection;

/**
 * AsseticConfiguration
 *
 * @author Étienne Dauvergne <contact@ekyna.com>
 */
class AsseticConfiguration
{
    /**
     * Builds the assetic configuration.
     *
     * @param array $config
     * @param string $root_dir
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
     * @param string $root_dir
     *
     * @return array
     */
    protected function buildCss(array $config)
    {
        $inputs = array(
            '@bootstrap_css',
            '@fontawesome_css',

            'bundles/ekynacore/css/lib/bootstrap.datepicker.css',
            'bundles/ekynacore/css/lib/select2.css',
            'bundles/ekynacore/css/form.css',
            'bundles/ekynacore/css/modal-gallery.css',

            'bundles/ekynaadmin/css/bootstrap-overrides.css',
            'bundles/ekynaadmin/css/lib/jquery-ui.css',
            'bundles/ekynaadmin/css/lib/uniform.default.css',
            'bundles/ekynaadmin/css/layout.css',
            'bundles/ekynaadmin/css/elements.css',
            'bundles/ekynaadmin/css/ui-elements.css',
            'bundles/ekynaadmin/css/show.css',
            'bundles/ekynaadmin/css/table.css',

            'bundles/ekynacms/css/content-editor.css',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('cssrewrite', 'yui_css'),
            'output'  => $config['output_dir'].'css/admin.css',
            'debug'   => false,
        );
    }

    /**
     * @param array $config
     * @param string $root_dir
     *
     * @return array
     */
    protected function buildJs(array $config)
    {        
        $inputs = array(
            '@jquery',
            '@bootstrap_js',

            '%kernel.root_dir%/../vendor/malsup/form/jquery.form.js',
            'bundles/ekynacore/js/jquery.autosize.min.js',
            'bundles/ekynacore/js/bootstrap.datepicker.js',
            'bundles/ekynacore/js/select2.min.js',
            'bundles/ekynacore/js/load-image.js',
            'bundles/ekynacore/js/modal-gallery.js',
            'bundles/ekynacore/js/string.prototypes.js',
            'bundles/ekynacore/js/forms.js',

            'bundles/ekynaadmin/js/jquery-ui.js',
            'bundles/ekynaadmin/js/jquery.uniform.min.js',
            'bundles/ekynaadmin/js/theme.js',
            'bundles/ekynaadmin/js/ui.js',

            'bundles/ekynacms/js/content-editor.js',
        );

        return array(
            'inputs'  => $inputs,
            'filters' => array('closure'),
            'output'  => $config['output_dir'].'js/admin.js',
            'debug'   => false,
        );
    }
}
