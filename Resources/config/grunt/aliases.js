module.exports = {
    'cssmin:admin': [
        'cssmin:admin_less',
        'cssmin:admin_css'
    ],
    'build:admin': [
        'clean:admin_pre',
        'copy:admin_img',
        'less:admin',
        'cssmin:admin',
        'uglify:admin_js',
        'clean:admin_post'
    ]
};
