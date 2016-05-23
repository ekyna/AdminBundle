module.exports = {
    'build:admin': [
        'clean:admin_pre',
        'copy:admin',
        'less:admin',
        'cssmin:admin',
        'uglify:admin',
        'clean:admin_post'
    ]
};
