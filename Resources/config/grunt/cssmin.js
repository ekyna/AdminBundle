module.exports = function (grunt, options) {
    return {
        admin: {
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/main.css': [
                    'bower_components/bootstrap/dist/css/bootstrap.css',
                    'bower_components/bootstrap3-dialog/dist/css/bootstrap-dialog.css', // TODO is in core
                    'bower_components/jquery-ui/themes/base/jquery-ui.css',
                    'bower_components/jquery-ui/themes/smoothness/jquery-ui.css',

                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/bootstrap.overrides.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/jquery-ui.overrides.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/layout.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/elements.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/ui-elements.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/show.css'
                ],
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/dashboard-shortcuts.css': [
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/dashboard-shortcuts.css'
                ],
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/login.css': [
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/login.css'
                ]
            }
        }
    }
};