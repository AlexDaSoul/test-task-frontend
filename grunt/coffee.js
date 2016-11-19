'use strict';

module.exports = function (path) {
    return {
        compile: {
            options: {
                sourceMap: true
            },
            files: {
                'web/assets/javascript/bundle.js': [
                    'app/assets/javascript/coffee/*.coffee'
                ]
            }
        }
    };
};
