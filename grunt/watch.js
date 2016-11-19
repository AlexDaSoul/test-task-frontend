'use strict';

module.exports = function (path) {
    return {
        js: {
            files: [
                'gruntfile.js',
                'grunt/*.js',
                'app/assets/javascript/coffee/**/*.coffee',
                'app/assets/styles/sass/**/*.scss'
            ],
            tasks: ['newer:coffee_jshint', 'newer:coffee', 'newer:sass', 'newer:autoprefixer']
        }
    };
};