module.exports = function (grunt, options) {
    return {
        admin: {
            options: {
                optimizationLevel: 6
            },
            files: [{
                expand: true,
                cwd: 'src/Ekyna/Bundle/AdminBundle/Resources/private/img/',
                src: ['**/*.{png,jpg,gif,svg,ico}'],
                dest: 'src/Ekyna/Bundle/AdminBundle/Resources/public/img/'
            }]
        }
    }
};