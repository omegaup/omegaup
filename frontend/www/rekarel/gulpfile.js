import gulp from "gulp"
import fileInclude from "gulp-file-include"
import replace from 'gulp-replace'
import insert  from 'gulp-insert'
import clean  from 'gulp-clean'
import htmlmin from 'gulp-html-minifier-terser'
import { createRequire } from 'module'

const require = createRequire(import.meta.url);

const manifest = require('./package.json');
const coreManifest = require('./node_modules/@rekarel/core/package.json');

const mainPath = "html/index.html"
const mainPathDist = "webapp/"

const docs = [
    "html/docs/index.html",
    "html/docs/java/*.html",
    "html/docs/pascal/*.html",
    "html/docs/rekarel/*.html",
    "html/docs/java_tutorial/*.html",
    "html/docs/pascal_tutorial/*.html",
];

const docsDist = "webapp/docs"

gulp.task('bundle-html', ()=> {
    return gulp.src([mainPath])
    .pipe(
        fileInclude({
            prefix: '@@',
            basepath: '@file',
            context: {
                shortVersion: manifest.version.replace(/[\.\-]/g,"_"),
                longVersion: manifest.version,
                coreVersion: manifest.dependencies["@rekarel/core"].replace(/^\^/, ""),
                languageVersion: coreManifest.rekarel.language
            }
        }))
    .pipe(htmlmin({
        collapseWhitespace:true,
        removeComments:true
    }))
    .pipe(gulp.dest(mainPathDist))
    
})
// Task to copy images
gulp.task('bundle-images', function () {
    return gulp.src('resources/img/**/*', {encoding:false})
      .pipe(gulp.dest('webapp/img'));
  });
  
  // Task to copy CSS
  gulp.task('bundle-css', function () {
    return gulp.src('resources/css/**/*')
      .pipe(gulp.dest('webapp/css'));
  });
  
  // Task to copy JavaScript
  gulp.task('bundle-js', function () {
    return gulp.src('resources/js/**/*')
      .pipe(gulp.dest('webapp/js'));
  });
  
  // Default task to run all copy tasks
  gulp.task('bundle-resources', gulp.parallel('bundle-images', 'bundle-css', 'bundle-js'));


gulp.task('bundle-docs', ()=> {
    return gulp.src(docs, { base: 'html/docs' })
    .pipe(
        fileInclude({
            prefix: '@@',
            basepath: '@file',
            context: {
                languageVersion: coreManifest.rekarel.language
            }
        }))        
    .pipe(htmlmin({
        collapseWhitespace:true,
        removeComments:true
    }))
    .pipe(gulp.dest(docsDist))
    
})
gulp.task('clean-docs', ()=> {
    return gulp.src(docsDist, {allowEmpty: true})    
        .pipe(clean());
    
})



gulp.task('process-jison-java', ()=> {

    const path = 'js/kareljava.js'
    return gulp.src(path) 
        .pipe(insert.append('\nfunction javaParser() {\n    return kareljava.parse.apply(kareljava, arguments);\n}\nexport {kareljava, javaParser }\n'))
        .pipe(replace("_token_stack:", '// _token_stack:'))
        .pipe(replace("loc: yyloc,", 'loc: lexer.yylloc, // Implement fix: https://github.com/zaach/jison/pull/356'))
        .pipe(gulp.dest('js/')); 

})


gulp.task('process-jison-pascal', ()=> {

    const path = 'js/karelpascal.js'
    return gulp.src(path) 
        .pipe(insert.append('\nfunction pascalParser () {\n    return karelpascal.parse.apply(karelpascal, arguments);\n}\nexport {karelpascal, pascalParser}'))
        .pipe(replace("_token_stack:", '// _token_stack:'))
        .pipe(replace("loc: yyloc,", 'loc: lexer.yylloc, // Implement fix: https://github.com/zaach/jison/pull/356'))
        .pipe(gulp.dest('js/')); 

})


gulp.task('process-jison-java2pascal', ()=> {

    const path = 'js/java2pascal.js'
    return gulp.src(path) 
        .pipe(insert.append('\nfunction java2pascalParser () {\n    return java2pascal.parse.apply(java2pascal, arguments);\n}\nexport {java2pascal, java2pascalParser}'))
        .pipe(replace("_token_stack:", '// _token_stack:'))
        .pipe(replace("loc: yyloc,", 'loc: lexer.yylloc, // Implement fix: https://github.com/zaach/jison/pull/356'))
        .pipe(gulp.dest('js/')); 

})


gulp.task('process-jison-pascal2java', ()=> {

    const path = 'js/pascal2java.js'
    return gulp.src(path) 
        .pipe(insert.append('\nfunction pascal2javaParser () {\n    return pascal2java.parse.apply(pascal2java, arguments);\n}\nexport {pascal2java, pascal2javaParser}'))
        .pipe(replace("_token_stack:", '// _token_stack:'))
        .pipe(replace("loc: yyloc,", 'loc: lexer.yylloc, // Implement fix: https://github.com/zaach/jison/pull/356'))
        .pipe(gulp.dest('js/')); 

})

const paths = {
    html: [
        './webapp/index.html',
        './webapp/ayuda.html',
        './webapp/ayuda-java.html',
        './webapp/ayuda-pascal.html',
    ],
    js: [
        './webapp/js/cindex.js',
    ],
    img: './webapp/img/*',
    css: './webapp/css/*',
    license: './LICENSE',
    build: './build'
};
gulp.task('clean-webapp', ()=> {
    return gulp.src('./webapp', {allowEmpty: true, read: false})
    .pipe(clean());
})
gulp.task('clean', ()=> {
    return gulp.src(paths.build, {allowEmpty: true, read: false})
    .pipe(clean());
});

gulp.task('copy-html', () => {
    return gulp.src(paths.html)
        .pipe(gulp.dest(paths.build));
});

gulp.task('copy-js', () => {
    return gulp.src(paths.js)
        .pipe(gulp.dest(`${paths.build}/js`));
});

gulp.task('copy-img', () => {
    return gulp.src(paths.img)
        .pipe(gulp.dest(`${paths.build}/img`));
});

gulp.task('copy-css', () => {
    return gulp.src(paths.css)
        .pipe(gulp.dest(`${paths.build}/css`));
});
gulp.task('copy-license', () => {
    return gulp.src(paths.license)
        .pipe(gulp.dest(`${paths.build}`));
});

 gulp.task('default', gulp.series('bundle-html', 'clean-docs', 'bundle-docs', 'bundle-resources'));
 gulp.task('build', gulp.series('clean', 'copy-html', 'copy-js', 'copy-img', 'copy-css','copy-license'));
