module.exports = function (grunt, options) {
    return {
        admin: {
            files: {
                'src/Ekyna/Bundle/AdminBundle/Resources/public/js/main.js': [
                    'src/Ekyna/Bundle/AdminBundle/Resources/private/js/main.js'
                ]
            }
        }
    }
};
