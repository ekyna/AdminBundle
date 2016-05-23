module.exports = function (grunt, options) {
    return {
        admin: {
            files: [
                {
                    expand: true,
                    cwd: 'bower_components/jquery-ui/themes/smoothness/images',
                    src: ['**'],
                    dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/css/images'
                },
                {
                    expand: true,
                    cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private',
                    src: ['img/**'],
                    dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public'
                }
            ]
        }
    }
};
