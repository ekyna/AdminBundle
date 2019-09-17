module.exports = function (grunt, options) {
    return {
        admin_less: { // For watch:admin_less
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/main.css': [
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/bootstrap.overrides.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/layout.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/elements.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/ui-elements.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/css/show.css',
                    'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/side-detail.css',
                    'src/Ekyna/Bundle/CoreBundle/Resources/public/css/ui.css'
                ]
            }
        }
    }
};
