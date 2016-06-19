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
        }
    }
};
