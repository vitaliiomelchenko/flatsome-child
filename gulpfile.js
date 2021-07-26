"use strict";

const gulp            = require('gulp');
const sass            = require('gulp-sass');
const concat          = require('gulp-concat');
const prefix          = require('gulp-autoprefixer');
const cssnano         = require('gulp-cssnano');
const rename          = require('gulp-rename');
const webpackStream   = require('webpack-stream');
const runSequence = require('run-sequence');

function css() {
  return gulp
    .src("sass/screen.sass")
    .pipe(sass().on('error', sass.logError))
    .pipe(cssnano())
    .pipe(concat('main.css'))
    .pipe(rename({suffix: '.min'}))
    .pipe(prefix('last 2 versions'))
    .pipe(gulp.dest("dist"))
};



gulp.task('js', function(){
  return gulp.src('js/main.js')
    .pipe(webpackStream({
      entry: './js/main.js',
      output: {
        filename: 'main.min.js',
      },
      module: {
        rules: [
          {
            test: /\.(js)$/,
            exclude: /(node_modules)/,
            loader: 'babel-loader'
          }
        ]
      },
      externals: {
        jquery: 'jQuery',
        window: 'window'
      },
    }))
    .pipe(gulp.dest('dist'))
});

// gulp.task('vendorcss', function(){
//   return gulp.src('assets/styles/libs/*.css')
//         .pipe(concat('vendors.min.css'))
//         .pipe(gulp.dest('assets/dist'));
// });

gulp.task('vendorjs', function(){
  return gulp.src('js/vendors/*.js')
        .pipe(concat('vendors.min.js'))
        .pipe(gulp.dest('dist'));
});

function watchFiles() {
  gulp.watch('sass/**/*.sass', css);
  //gulp.watch('js/**/*.js', ['js']);
  // Other watchers
}

//gulp.task('start', ['js', 'sass'])
//gulp.task('start', gulp.parallel('sass'));

// gulp.task('default', function(cb) {
//   runSequence('start', 'watch', cb);
// });

const build = gulp.series(css, watchFiles);
const watch = gulp.parallel(watchFiles);

//gulp.task('default', gulp.parallel('sass', 'watch'));

// export tasks
exports.css = css;
exports.watch = watchFiles;
exports.build = build;
exports.default = build;
