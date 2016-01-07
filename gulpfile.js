// Props to http://mikevalstar.com/post/fast-gulp-browserify-babelify-watchify-react-build/ for most of this file.
'use strict';

var gulp = require('gulp');  // Base gulp package
var babelify = require('babelify'); // Used to convert ES6 & JSX to ES5
var browserify = require('browserify'); // Providers "require" support, CommonJS
var notify = require('gulp-notify'); // Provides notification to both the console and Growel
var rename = require('gulp-rename'); // Rename sources
var sourcemaps = require('gulp-sourcemaps'); // Provide external sourcemap files
var livereload = require('gulp-livereload'); // Livereload support for the browser
var gutil = require('gulp-util'); // Provides gulp utilities, including logging and beep
var chalk = require('chalk'); // Allows for coloring for logging
var source = require('vinyl-source-stream'); // Vinyl stream support
var buffer = require('vinyl-buffer'); // Vinyl stream support
var watchify = require('watchify'); // Watchify for source changes
var merge = require('utils-merge'); // Object merge tool
var duration = require('gulp-duration'); // Time aspects of your gulp process
var uglify = require('gulp-uglify'); // Minify the JS.

// Configuration for Gulp
var config = {
    js: {
        src: 'assets/src/js/batch.jsx',
        watch: 'assets/src/js/**/*',
        outputDir: 'assets/dist/',
        outputFile: 'batch.min.js',
    },
};

gulp.task( 'javascript', function() {
    livereload.listen(); // Start livereload server

    var args = merge( watchify.args, { debug: true } ); // Merge in default watchify args with browserify arguments

    var bundler = browserify( config.js.src, args ) // Browserify
        .plugin( watchify, {
            ignoreWatch: ['**/node_modules/**', '**/bower_components/**']
        } ) // Watchify to watch source file changes
        .transform( babelify, {
            presets: ['es2015', 'react']
        } );

    bundle( bundler ); // Run the bundle the first time (required for Watchify to kick in)

    bundler.on( 'update', function() {
        bundle( bundler ); // Re-run bundle on source updates
    });
} );

// Completes the final file outputs
function bundle( bundler ) {
    var bundleTimer = duration( 'Javascript bundle time' );

    return bundler
        .bundle()
        .pipe( source('batch.jsx')) // Set source name
        .pipe( buffer() ) // Convert to gulp pipeline
        .pipe( rename( config.js.outputFile ) ) // Rename the output file
        .pipe( sourcemaps.init( { loadMaps: true } ) ) // Extract the inline sourcemaps
        .pipe( uglify() ) // Minify the JS.
        .pipe( sourcemaps.write( './map' ) ) // Set folder for sourcemaps to output to
        .pipe( gulp.dest( config.js.outputDir) ) // Set the output folder
        .pipe( notify( {
            message: 'Generated file: <%= file.relative %>',
        } ) ) // Output the file being created
        .pipe( bundleTimer ) // Output time timing of the file creation
        .pipe( livereload() ); // Reload the view in the browser
}

// Gulp task for build
gulp.task( 'default', [ 'javascript' ] );
