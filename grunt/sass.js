'use strict';

module.exports = function (path) {
    return {
        build: {
            files: [{
                'web/assets/styles/bundle.css': ['app/assets/styles/sass/**/*.scss']
            }]
        }
    };
};