var gulp = require('gulp');
var $ = require('gulp-load-plugins')();

//gulp.task('sass', function () {
//    return gulp.src('./assets/src/scss/psp-slack-admin.min.scss')
//        .pipe($.sass()
//            .on('error', $.sass.logError))
//        .pipe($.sourcemaps.init())
//        .pipe($.autoprefixer({
//            browsers: ['last 2 versions', 'ie >= 9']
//        }))
//        .pipe($.sass({outputStyle: 'compressed'}))
//        .pipe($.sourcemaps.write())
//        .pipe(gulp.dest('./assets/dist/css/'))
//        .pipe($.notify({ message: 'Sass task complete' }));
//});

gulp.task('scripts', function () {
    return gulp.src('./assets/js/admin/feed_settings.js')
        .pipe($.rename('feed_settings.min.js'))
        .pipe($.uglify())
        .pipe(gulp.dest('./assets/js/admin/'))
        .pipe($.notify({message: 'Scripts task complete'}));
});

gulp.task('default', ['scripts'], function () {
    gulp.watch(['./assets/js/**/*.js'], ['scripts']);
});
