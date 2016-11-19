'use strict';

module.exports = function (path) {
    return {
        files: {
            options: {
                globals: ['console', '$', '_', 'window', 'document', 'Backbone', 'Marionette', 'App', 'moment']
            },
            src: 'app/assets/javascript/coffee/*'
        }
    };
};