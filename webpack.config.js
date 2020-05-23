const fs = require('fs');
const path = require('path');
const webpack = require('webpack');

const CopyWebpackPlugin = require('copy-webpack-plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');
const RemoveSourceWebpackPlugin = require('remove-source-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const omegaupStylesRegExp = /omegaup_styles\.js/;
const defaultBadgeIcon = fs.readFileSync('./frontend/badges/default_icon.svg');

let config = [
  {
    name: 'frontend',
    entry: {
      omegaup: [
        '@babel/polyfill',
        'unfetch/polyfill',
        './frontend/www/js/omegaup/polyfills.js',
        './frontend/www/js/omegaup/omegaup.js',
      ],
      activity_feed: './frontend/www/js/omegaup/activity/feed.js',
      admin_roles: './frontend/www/js/omegaup/admin/roles.js',
      admin_support: './frontend/www/js/omegaup/admin/support.js',
      admin_user: './frontend/www/js/omegaup/admin/user.js',
      arena: './frontend/www/js/omegaup/arena/arena.ts',
      arena_admin: './frontend/www/js/omegaup/arena/admin.ts',
      arena_assignment: './frontend/www/js/omegaup/arena/assignment.ts',
      arena_assignment_admin: './frontend/www/js/omegaup/arena/assignment_admin.ts',
      arena_contest: './frontend/www/js/omegaup/arena/contest.ts',
      arena_contest_list: './frontend/www/js/omegaup/arena/contest_list.ts',
      arena_scoreboard: './frontend/www/js/omegaup/arena/scoreboard.ts',
      arena_virtual: './frontend/www/js/omegaup/arena/virtual.js',
      authors_rank: './frontend/www/js/omegaup/user/authors_rank.ts',
      badge_details: './frontend/www/js/omegaup/badge/details.ts',
      badge_list: './frontend/www/js/omegaup/badge/list.js',
      coder_of_the_month: './frontend/www/js/omegaup/coderofthemonth/index.ts',
      common_footer: './frontend/www/js/omegaup/common/footer.js',
      common_footer_v2: './frontend/www/js/omegaup/common/footer_v2.ts',
      common_index: './frontend/www/js/omegaup/common/index.ts',
      common_navbar: './frontend/www/js/omegaup/common/navbar.ts',
      common_navbar_v2: './frontend/www/js/omegaup/common/navbar_v2.ts',
      common_stats: './frontend/www/js/omegaup/common/stats.ts',
      contest_edit: './frontend/www/js/omegaup/contest/edit.js',
      contest_intro: './frontend/www/js/omegaup/contest/intro.ts',
      contest_list: './frontend/www/js/omegaup/contest/list.js',
      contest_list_participant:
        './frontend/www/js/omegaup/contest/list_participant.js',
      contest_new: './frontend/www/js/omegaup/contest/new.ts',
      contest_print: './frontend/www/js/omegaup/contest/print.ts',
      contest_report: './frontend/www/js/omegaup/contest/report.js',
      contest_scoreboardmerge:
        './frontend/www/js/omegaup/contest/scoreboardmerge.js',
      course_details: './frontend/www/js/omegaup/course/details.js',
      course_edit: './frontend/www/js/omegaup/course/edit.js',
      course_intro: './frontend/www/js/omegaup/course/intro.js',
      course_list: './frontend/www/js/omegaup/course/list.ts',
      course_new: './frontend/www/js/omegaup/course/new.js',
      course_scoreboard: './frontend/www/js/omegaup/course/scoreboard.js',
      course_student: './frontend/www/js/omegaup/course/student.js',
      course_students: './frontend/www/js/omegaup/course/students.js',
      group_identities: './frontend/www/js/omegaup/group/identities.js',
      group_members: './frontend/www/js/omegaup/group/members.js',
      course_submissions_list:
        './frontend/www/js/omegaup/course/submissions_list.js',
      group_list: './frontend/www/js/omegaup/group/list.js',
      login_password_recover: './frontend/www/js/omegaup/login/recover.ts',
      login_password_reset: './frontend/www/js/omegaup/login/reset.ts',
      problem_admins: './frontend/www/js/omegaup/problem/admins.ts',
      problem_edit: './frontend/www/js/omegaup/problem/edit.js',
      problem_edit_form: './frontend/www/js/omegaup/problem/edit.ts',
      problem_feedback: './frontend/www/js/omegaup/problem/feedback.js',
      problem_list: './frontend/www/js/omegaup/problem/list.ts',
      problem_mine: './frontend/www/js/omegaup/problem/mine.js',
      problem_new: './frontend/www/js/omegaup/problem/new.ts',
      problem_print: './frontend/www/js/omegaup/problem/print.ts',
      problem_solution: './frontend/www/js/omegaup/problem/solution.js',
      problem_tags: './frontend/www/js/omegaup/problem/tags.ts',
      qualitynomination_popup:
        './frontend/www/js/omegaup/arena/qualitynomination_popup.js',
      qualitynomination_list:
        './frontend/www/js/omegaup/qualitynomination/list.js',
      qualitynomination_demotionpopup:
        './frontend/www/js/omegaup/arena/qualitynomination_demotionpopup.js',
      qualitynomination_details:
        './frontend/www/js/omegaup/qualitynomination/details.js',
      qualitynomination_qualityreview:
        './frontend/www/js/omegaup/arena/qualitynomination_qualityreview.js',
      schools_intro: './frontend/www/js/omegaup/schools/intro.js',
      school_of_the_month:
        './frontend/www/js/omegaup/schools/schoolofthemonth.ts',
      school_profile: './frontend/www/js/omegaup/schools/profile.ts',
      schools_rank: './frontend/www/js/omegaup/schools/rank.ts',
      submissions_list: './frontend/www/js/omegaup/submissions/list.ts',
      user_basic_edit: './frontend/www/js/omegaup/user/basicedit.js',
      user_edit_email_form: './frontend/www/js/omegaup/user/emailedit.js',
      user_manage_identities:
        './frontend/www/js/omegaup/user/manage_identities.js',
      user_profile: './frontend/www/js/omegaup/user/profile.js',
      user_privacy_policy: './frontend/www/js/omegaup/user/privacy_policy.js',
      users_rank: './frontend/www/js/omegaup/user/rank.ts',
    },
    output: {
      path: path.resolve(__dirname, './frontend/www/'),
      publicPath: '/',
      filename: 'js/dist/[name].js',
      library: '[name]',
      libraryTarget: 'umd',
    },
    plugins: [
      new CopyWebpackPlugin([
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
      ]),
      new HtmlWebpackPlugin({
        inject: false,
        chunks: ['omegaup', 'arena'],
        filename: 'tests/index.html',
        template: path.resolve(__dirname, './stuff/webpack/tests.ejs'),
      }),
      new VueLoaderPlugin(),
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
            name: module => {
              const packageName = module.context.match(
                /\/node_modules\/([^@\/]+)/,
              )[1];

              return `npm.${packageName}`;
            },
            test: /\/node_modules\/[^@\/]+/,
            chunks: 'initial',
            minChunks: 2,
            minSize: 50 * 1024,
            priority: 10,
          },
          iso_3166_2_js: {
            name: 'iso-3166-2.js',
            test: /\/frontend\/www\/third_party\/js\/iso-3166-2.js\//,
            chunks: 'all',
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
      rules: [
        {
          test: /\.vue$/,
          loader: 'vue-loader',
          options: {
            loaders: {},
          },
        },
        {
          test: /\.tsx?$/,
          loader: 'ts-loader',
          exclude: /node_modules/,
          options: {
            appendTsSuffixTo: [/\.vue$/],
          },
        },
        {
          test: /\.js$/,
          loader: 'babel-loader?cacheDirectory',
          exclude: /node_modules/,
        },
        {
          test: /\.(png|jpg|gif|svg)$/,
          loader: 'file-loader',
          options: { name: '[name].[ext]?[hash]' },
        },
        {
          test: /\.css$/,
          loader: 'style-loader!css-loader',
        },
        // inline scss styles on vue components
        {
          test: /\.scss$/,
          use: ['vue-style-loader', 'css-loader', 'sass-loader'],
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
    devServer: { historyApiFallback: true, noInfo: true },
    performance: { hints: false },
    devtool: 'cheap-source-map',
    watchOptions: {
      aggregateTimeout: 300,
      poll: 1000,
      ignored: /node_modules/,
    },
  },
  {
    name: 'style',
    entry: {
      omegaup_styles: './frontend/www/sass/main.scss',
    },
    output: {
      path: path.resolve(__dirname, './frontend/www/'),
      publicPath: '/',
    },
    plugins: [
      new ExtractTextPlugin({
        filename: 'css/dist/[name].css',
        allChunks: true,
      }),
      new RemoveSourceWebpackPlugin([omegaupStylesRegExp]),
    ],
    module: {
      rules: [
        {
          test: /\.scss$/,
          loader: ExtractTextPlugin.extract({
            fallback: 'style-loader',
            use: ['css-loader', 'sass-loader'],
          }),
        },
      ],
    },
    devtool: 'cheap-source-map',
  },
  {
    name: 'grader',
    entry: {
      grader_ephemeral: [
        '@babel/polyfill',
        './frontend/www/js/omegaup/grader/ephemeral.js',
      ],
    },
    optimization: {},
    module: {
      rules: [
        {
          test: /\.vue$/,
          loader: 'vue-loader',
          options: {
            loaders: {},
          },
        },
        {
          test: /\.js$/,
          exclude: /node_modules/,
          use: {
            loader: 'babel-loader?cacheDirectory',
            options: {
              presets: ['@babel/env'],
            },
          },
        },
        {
          test: /\.css$/,
          loader: 'style-loader!css-loader',
        },
      ],
    },
    resolve: {
      alias: {
        vue$: 'vue/dist/vue.common.js',
        'vue-async-computed': 'vue-async-computed/dist/vue-async-computed.js',
      },
    },
    plugins: [
      new VueLoaderPlugin(),
      new MonacoWebpackPlugin({
        output: './js/dist',
      }),
    ],
    output: {
      path: path.resolve(__dirname, './frontend/www/'),
      publicPath: '/',
      filename: 'js/dist/[name].js',
      library: '[name]',
      libraryTarget: 'umd',
    },
    devtool: 'cheap-source-map',
  },
];

module.exports = (env, argv) => {
  if (argv.mode !== 'development') {
    for (const entry of config) {
      entry.devtool = 'source-map';
    }
  }
  for (const entry of config) {
    if (entry.name != 'frontend') {
      continue;
    }
    // Generate the JSON dependency objects.
    for (const entryname of Object.keys(entry.entry)) {
      entry.plugins.push(
        new HtmlWebpackPlugin({
          inject: false,
          chunks: [entryname],
          filename: `js/dist/${entryname}.deps.json`,
          template: path.resolve(__dirname, './stuff/webpack/deps.ejs'),
        }),
      );
    }
  }
  return config;
};
