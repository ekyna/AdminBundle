module.exports = function (grunt, options) {
    // @see https://github.com/gruntjs/grunt-contrib-less
    return {
        admin: {
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/tmp/css/bootstrap.css':
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/less/bootstrap.less'
            }
        }
    }
};
