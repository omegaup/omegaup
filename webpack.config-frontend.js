const fs = require('fs');
const path = require('path');

const CopyWebpackPlugin = require('copy-webpack-plugin');
const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');
const defaultBadgeIcon = fs.readFileSync('./frontend/badges/default_icon.svg');

module.exports = {
  name: 'frontend',

  entry: {
    omegaup: [
      '@babel/polyfill',
      'unfetch/polyfill',
      './frontend/www/js/omegaup/polyfills.js',
      './frontend/www/js/omegaup/omegaup-legacy.js',
    ],
    activity_feed: './frontend/www/js/omegaup/activity/feed.ts',
    admin_roles: './frontend/www/js/omegaup/admin/roles.ts',
    admin_support: './frontend/www/js/omegaup/admin/support.ts',
    admin_user: './frontend/www/js/omegaup/admin/user.ts',
    arena_contest_contestant:
      './frontend/www/js/omegaup/arena/contest_contestant.ts',
    arena_contest_listv2: './frontend/www/js/omegaup/arena/contest_listv2.ts',
    arena_contest_practice:
      './frontend/www/js/omegaup/arena/contest_practice.ts',
    arena_course: './frontend/www/js/omegaup/arena/course.ts',
    arena_coursev2: './frontend/www/js/omegaup/arena/coursev2.ts',
    arena_contest_virtual: './frontend/www/js/omegaup/arena/contest_virtual.ts',
    arena_global_runs: './frontend/www/js/omegaup/arena/global_runs.ts',
    authors_rank: './frontend/www/js/omegaup/user/authors_rank.ts',
    badge_details: './frontend/www/js/omegaup/badge/details.ts',
    badge_list: './frontend/www/js/omegaup/badge/list.ts',
    certificate_details: './frontend/www/js/omegaup/certificate/details.ts',
    certificate_mine: './frontend/www/js/omegaup/certificate/mine.ts',
    certificate_validation:
      './frontend/www/js/omegaup/certificate/validation.ts',
    coder_of_the_month: './frontend/www/js/omegaup/coderofthemonth/index.ts',
    common_scroll_to_top: './frontend/www/js/omegaup/common/scroll_to_top.ts',
    common_docs: './frontend/www/js/omegaup/common/docs.ts',
    common_footer: './frontend/www/js/omegaup/common/footer.ts',
    common_index: './frontend/www/js/omegaup/common/index.ts',
    common_navbar: './frontend/www/js/omegaup/common/navbar.ts',
    common_global_notifications:
      './frontend/www/js/omegaup/common/global_notifications.ts',
    common_stats: './frontend/www/js/omegaup/common/stats.ts',
    contest_edit: './frontend/www/js/omegaup/contest/edit.ts',
    contest_intro: './frontend/www/js/omegaup/contest/intro.ts',
    contest_mine: './frontend/www/js/omegaup/contest/mine.ts',
    contest_new: './frontend/www/js/omegaup/contest/new.ts',
    contest_print: './frontend/www/js/omegaup/contest/print.ts',
    contest_report: './frontend/www/js/omegaup/contest/report.ts',
    contest_scoreboard: './frontend/www/js/omegaup/arena/scoreboard.ts',
    contest_scoreboardmerge:
      './frontend/www/js/omegaup/contest/scoreboardmerge.ts',
    contest_virtual: './frontend/www/js/omegaup/contest/virtual.ts',
    course_clarifications: './frontend/www/js/omegaup/course/clarifications.ts',
    course_clone: './frontend/www/js/omegaup/course/clone.ts',
    course_details: './frontend/www/js/omegaup/course/details.ts',
    course_edit: './frontend/www/js/omegaup/course/edit.ts',
    course_homepage: './frontend/www/js/omegaup/course/homepage.ts',
    course_intro: './frontend/www/js/omegaup/course/intro.ts',
    course_mine: './frontend/www/js/omegaup/course/mine.ts',
    course_new: './frontend/www/js/omegaup/course/new.ts',
    course_scoreboard: './frontend/www/js/omegaup/course/scoreboard.ts',
    course_statistics: './frontend/www/js/omegaup/course/statistics.ts',
    course_student: './frontend/www/js/omegaup/course/student.ts',
    course_student_with_assignment:
      './frontend/www/js/omegaup/course/studentWithAssignment.ts',
    course_students: './frontend/www/js/omegaup/course/students.ts',
    course_submissions_list:
      './frontend/www/js/omegaup/course/submissions_list.ts',
    course_tabs: './frontend/www/js/omegaup/course/tabs.ts',
    grader_ide: './frontend/www/js/omegaup/grader/ide.ts',
    group_edit: './frontend/www/js/omegaup/group/edit.ts',
    group_list: './frontend/www/js/omegaup/group/list.ts',
    group_new: './frontend/www/js/omegaup/group/new.ts',
    group_scoreboard_contests:
      './frontend/www/js/omegaup/group/scoreboard_contests.ts',
    group_scoreboard_details:
      './frontend/www/js/omegaup/group/scoreboard_details.ts',
    libinteractive_gen: './frontend/www/js/omegaup/libinteractive/gen.ts',
    login_password_recover: './frontend/www/js/omegaup/login/recover.ts',
    login_password_reset: './frontend/www/js/omegaup/login/reset.ts',
    login_signin: './frontend/www/js/omegaup/login/signin.ts',
    logout: './frontend/www/js/omegaup/login/logout.ts',
    problem_creator: './frontend/www/js/omegaup/problem/creator/creator.ts',
    problem_collection: './frontend/www/js/omegaup/problem/collection.ts',
    problem_collection_list:
      './frontend/www/js/omegaup/problem/collection_list.ts',
    problem_collection_list_by_author:
      './frontend/www/js/omegaup/problem/collection_list_by_author.ts',
    problem_edit: './frontend/www/js/omegaup/problem/edit.ts',
    problem_details: './frontend/www/js/omegaup/problem/details.ts',
    problem_list: './frontend/www/js/omegaup/problem/list.ts',
    problem_mine: './frontend/www/js/omegaup/problem/mine.ts',
    problem_new: './frontend/www/js/omegaup/problem/new.ts',
    problem_print: './frontend/www/js/omegaup/problem/print.ts',
    problem_statement: './frontend/www/js/omegaup/problem/statement.ts',
    qualitynomination_list:
      './frontend/www/js/omegaup/qualitynomination/list.ts',
    qualitynomination_details:
      './frontend/www/js/omegaup/qualitynomination/details.js',
    school_of_the_month:
      './frontend/www/js/omegaup/schools/schoolofthemonth.ts',
    school_profile: './frontend/www/js/omegaup/schools/profile.ts',
    schools_rank: './frontend/www/js/omegaup/schools/rank.ts',
    submissions_list: './frontend/www/js/omegaup/submissions/list.ts',
    teams_group_edit: './frontend/www/js/omegaup/teamsgroup/edit.ts',
    teams_group_list: './frontend/www/js/omegaup/teamsgroup/list.ts',
    teams_group_new: './frontend/www/js/omegaup/teamsgroup/new.ts',
    user_dependents: './frontend/www/js/omegaup/user/dependents.ts',
    user_edit_email_form: './frontend/www/js/omegaup/user/emailedit.ts',
    user_profile: './frontend/www/js/omegaup/user/profile.ts',
    user_privacy_policy: './frontend/www/js/omegaup/user/privacy_policy.ts',
    user_verification_parental_token:
      './frontend/www/js/omegaup/user/verification_parental_token.ts',
    users_rank: './frontend/www/js/omegaup/user/rank.ts',
  },

  output: {
    path: path.resolve(__dirname, './frontend/www/'),
    publicPath: '/',
    filename: 'js/dist/[name].js',
    library: '[name]',
    libraryTarget: 'umd',

    // use absolute paths in sourcemaps (important for debugging via IDE)
    devtoolModuleFilenameTemplate: '[absolute-resource-path]',
    devtoolFallbackModuleFilenameTemplate: '[absolute-resource-path]?[hash]',
  },

  plugins: [
    new MonacoWebpackPlugin({
      filename: 'js/dist/[name].worker.js',
    }),
    new CopyWebpackPlugin({
      patterns: [
        {
          from: './frontend/badges/**/query.sql',
          to: path.resolve(__dirname, './frontend/www/media/dist/badges'),
          transform(content, filepath) {
            const iconPath = `${path.dirname(filepath)}/icon.svg`;
            return fs.existsSync(iconPath)
              ? fs.readFileSync(iconPath)
              : defaultBadgeIcon;
          },
          transformPath(targetPath, absolutePath) {
            return `media/dist/badges/${path.basename(
              path.dirname(absolutePath),
            )}.svg`;
          },
        },
      ],
    }),
    new VueLoaderPlugin(),
    new ForkTsCheckerWebpackPlugin({
      typescript: {
        extensions: {
          vue: true,
        },
      },
      formatter: 'codeframe',
      async: false,
    }),
  ],

  optimization: {
    runtimeChunk: {
      name: 'commons',
    },
    splitChunks: {
      maxInitialRequests: Infinity,
      cacheGroups: {
        core_js: {
          name: 'npm.core-js',
          test: /\/node_modules\/core-js\//,
          chunks: 'all',
          priority: 20,
        },
        jszip: {
          name: 'npm.jszip',
          test: /\/node_modules\/jszip\//,
          chunks: 'all',
          priority: 20,
        },
        vendor: {
          name: (module) => {
            const packageName = module.context.match(
              /\/node_modules\/([^@/]+)/,
            )[1];

            return `npm.${packageName}`;
          },
          test: /\/node_modules\/[^@/]+/,
          chunks: 'initial',
          minChunks: 2,
          minSize: 50 * 1024,
          priority: 10,
        },
        commons: {
          name: 'commons',
          chunks: 'initial',
          minChunks: 4,
          priority: 1,
        },
        default: {
          reuseExistingChunk: true,
        },
      },
    },
  },

  module: {
    noParse: /^(vue|vue-router|vuex|vuex-router-sync)$/,
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          compilerOptions: {
            whitespace: 'condense',
          },
          optimizeSSR: false,
        },
      },
      {
        test: /\.ts$/,
        loader: 'ts-loader',
        exclude: [/node_modules/, /stories\.ts$/],
        options: {
          appendTsSuffixTo: [/\.vue$/],
          transpileOnly: true,
          happyPackMode: false,
        },
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        options: {
          presets: ['@babel/env'],
          cacheDirectory: true,
        },
        exclude: /node_modules/,
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        loader: 'file-loader',
        options: { name: '[name].[ext]?[hash]' },
      },
      // inline scss styles on vue components
      {
        test: /\.css$/,
        use: ['vue-style-loader', 'css-loader'],
      },
      {
        test: /\.scss$/,
        use: ['vue-style-loader', 'css-loader', 'sass-loader'],
      },
      {
        test: /\.ttf$/,
        type: 'asset/resource',
        generator: {
          filename: 'js/dist/[name][ext]',
        },
      },
    ],
  },

  resolve: {
    extensions: ['.ts', '.js', '.vue', '.json'],
    alias: {
      vue$: 'vue/dist/vue.common.js',
      'vue-async-computed': 'vue-async-computed/dist/vue-async-computed.js',
      jszip: 'jszip/dist/jszip.js',
      pako: 'pako/dist/pako.min.js',
      '@': path.resolve(__dirname, './frontend/www/'),
    },
  },
};
