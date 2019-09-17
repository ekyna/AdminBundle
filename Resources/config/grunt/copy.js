module.exports = function (grunt, options) {
    return {
        admin_less: { // For watch:admin_less
            expand: true,
            cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css',
            src: ['**'],
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/css'
        },
        admin_js: { // for watch:admin_js
            expand: true,
            cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/js',
            src: ['**'],
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/js'
        }
    }
};
