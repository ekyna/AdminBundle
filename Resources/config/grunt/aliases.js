module.exports = {
    'build:admin': [
        'clean:admin_pre',
        'imagemin:admin',
        'less:admin',
        'cssmin:admin_less',
        'uglify:admin_js',
        'clean:admin_post'
    ]
};
