'use strict';

module.exports = function (path) {
    return {
        dist: {
            files: [{
                expand: true,
                cwd: 'web/assets/styles/',
                src: ['*.css', '!*.min.css'],
                dest: 'web/assets/styles/'
            }]
        }
    };
};