module.exports = function (grunt, options) {
    return {
        admin_less: {
            files: ['src/Ekyna/Bundle/AdminBundle/Resources/private/less/**/*.less'],
            tasks: ['less:admin', 'copy:admin_less', 'clean:admin_post'],
            options: {
                spawn: false
            }
        },
        admin_js: {
            files: ['src/Ekyna/Bundle/AdminBundle/Resources/private/js/*.js'],
            tasks: ['copy:admin_js'],
            options: {
                spawn: false
            }
        },
        admin_css: {
            files: ['src/Ekyna/Bundle/AdminBundle/Resources/private/css/*.css'],
            tasks: ['cssmin:admin_css'],
            options: {
                spawn: false
            }
        }
    }
};
