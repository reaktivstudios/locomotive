var gulp = require( 'gulp' );
var concat = require( 'gulp-concat' );
var uglify = require( 'gulp-uglify' );
var react = require( 'gulp-react' );

var path = {
    JS: [
        'assets/src/js/*.js',
        'assets/src/js/**/*.js'
    ],
    MINIFIED_OUT: 'build.min.js',
    DEST: 'assets/dist'
};

gulp.task( 'build', function() {
    gulp.src( path.JS )
        .pipe( react() )
        .pipe( concat( path.MINIFIED_OUT ) )
        .pipe( uglify() )
        .pipe( gulp.dest( path.DEST ) );
});

gulp.task( 'watch', function(){
    gulp.watch( path.JS, [ 'build' ] );
});

gulp.task( 'default', [ 'watch' ] );
