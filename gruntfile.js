'use strict';

var path = require('path');
var argv = require('yargs').argv;

module.exports = function (grunt) {
    /** Load all grunt related task */
    require('load-grunt-tasks')(grunt);
    require('time-grunt')(grunt);

    grunt.initConfig({
        pkg: grunt.file.readJSON('package.json'),

        /** newer task */
        newer: require('grunt-newer'),

        /** CoffeeScript task */
        coffee: require('./grunt/coffee')(path),

        /** coffee-jshint task */
        coffee_jshint: require('./grunt/coffee_jshint')(path),

        /** Sass task */
        sass: require('./grunt/sass')(path),

        /** Autoprefixer task */
        autoprefixer: require('./grunt/autoprefixer')(path),

        /** Watch task */
        watch: require('./grunt/watch')(path),

        /** Cssmin task */
        cssmin: require('./grunt/cssmin')(path),

        /** uglify task */
        uglify: require('./grunt/uglify')(path)
    });


    /**
     * dev task
     * @usage: grunt dev
     */

    grunt.registerTask('dev', [
        'coffee_jshint',
        'coffee',
        'sass',
        'autoprefixer',
        'watch'
    ]);

    /**
     * serve task
     * @usage: grunt serve
     */
    var distTasks = [
        'coffee_jshint',
        'coffee',
        'sass',
        'autoprefixer'
    ];

    /** if --min flag is present */
    if (argv.min) {
        distTasks.push('cssmin', 'uglify');
    }

    grunt.registerTask('dist', distTasks);
};