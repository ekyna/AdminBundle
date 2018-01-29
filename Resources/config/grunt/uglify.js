module.exports = function (grunt, options) {
    return {
        admin_js: {
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/js',
                src: '**/*.js',
                dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/js'
            }]
        }
    }
};
