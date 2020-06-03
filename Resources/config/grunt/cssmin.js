module.exports = function (grunt, options) {
    return {
        admin_less: {
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/admin.css':
                    'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/admin.css',
                'src/Ekyna/Bundle/AdminBundle/Resources/public/css/login.css':
                    'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/login.css'
            }
        }
    }
};
