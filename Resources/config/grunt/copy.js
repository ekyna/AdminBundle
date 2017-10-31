module.exports = function (grunt, options) {
    return {
        admin: {
            files: [
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private',
                    src: ['img/**'],
                    dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public'
                }
            ]
        },
        admin_js: { // for watch:admin_js
            expand: true,
            cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/js',
            src: ['**'],
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/js'
        },
        admin_css: { // for watch:admin_css
            expand: true,
            cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/css',
            src: ['browser.css', 'form.css'],
            dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/css'
        }
    }
};
