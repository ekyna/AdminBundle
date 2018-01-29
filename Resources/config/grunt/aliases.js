module.exports = {
    'build:admin': [
        'clean:admin_pre',
        'copy:admin',
        'cssmin:admin',
        'uglify:admin_js'
    ]
};
