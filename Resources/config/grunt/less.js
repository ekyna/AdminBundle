module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        admin: {
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/admin.css':
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/less/admin.less',
                'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/login.css':
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/less/login.less'
            }
        }
    }
};
