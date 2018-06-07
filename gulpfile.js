var gulp = require('gulp')
var sass = require('gulp-sass')

gulp.task('sass', function() {
  return gulp.src('./frontend/www/sass/**/*.scss')
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest('./frontend/www/css/dist'))
})

gulp.task('sass:watch', function() {
  gulp.watch('./frontend/www/sass/**/*.scss', ['sass'])
})