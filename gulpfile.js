var gulp = require("gulp"),
    less = require("gulp-less"),
    concat = require("gulp-concat"),
    uglify = require("gulp-uglify"),
    cssmin = require("gulp-cssmin");
    csslint = require('gulp-csslint');
    LessPluginCleanCSS = require('less-plugin-clean-css'),
    LessPluginAutoPrefix = require('less-plugin-autoprefix'),
    cleancss = new LessPluginCleanCSS({ advanced: true }),
    autoprefix = new LessPluginAutoPrefix({ browsers: ['> 3%', 'last 2 versions', 'IE 8'] });

var sourceLessUser = 'www/assets/less/';
var targetCssDir = 'www/';


gulp.task('compileLessUser', function() {
  return gulp.src(sourceLessUser+'main.less')
  	.pipe(concat('style.less'))
    .pipe(less({
    	plugins: [autoprefix, cleancss]
    }))
    .on('error', swallowError)
    .pipe(gulp.dest(targetCssDir))
});

// reports css warnings
gulp.task('cssLint', function() {
  gulp.src(targetCssDir + '*.css')
    .pipe(csslint())
    .pipe(csslint.reporter());
});

gulp.task('watch', function() {
  // gulp.watch(sourceLessUser+'/*.less', ['compileLessUser']);
  gulp.watch(sourceLessUser+'**/*.less', ['compileLessUser']);

});

function swallowError (error) {

    //If you want details of the error in the console
    console.log(error.toString());

    this.emit('end');
}

// gulp.task('clean', function(cb) {
//   return del([
//       'www/assets/css/*',
//     ], cb);
// });

// gulp.task('compressJs', function() {
//   gulp.src(['lib/*.js'])
//     .pipe(uglify())
//     .pipe(gulp.dest('dist'))
// });

// gulp.task('default', function() {
//   gulp.run('compileLess');
//   // gulp.run('cssLint');
// });
