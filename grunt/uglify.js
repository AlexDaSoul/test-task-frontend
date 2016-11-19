'use strict';

module.exports = function (path) {
    return {
        dist: {
            files: [{
                expand: true,
                cwd: 'web/assets/javascript/',
                src: ['*.js', '!*.min.js'],
                dest: 'web/assets/javascript/'
            }]
        }
    };
};