module.exports = function (grunt, options) {
    return {
        admin_less: { // For watch:app_less
            src: 'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/bootstrap.css',
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/css/bootstrap.css'
        },
        admin_js: { // for watch:admin_js
            expand: true,
            cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/js',
            src: ['**'],
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/js'
        }
    }
};
