const path = require('path');
const webpack = require('webpack');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const ExtractTextPlugin = require('extract-text-webpack-plugin');
const RemoveSourceWebpackPlugin = require('remove-source-webpack-plugin');

const omegaupStylesRegExp = /omegaup_styles\.js/;

module.exports = {
  entry: {
    omegaup: [
      'babel-polyfill',
      './frontend/www/js/omegaup/polyfills.js',
      './frontend/www/js/omegaup/omegaup.js'
    ],
    activity_feed: './frontend/www/js/omegaup/activity/feed.js',
    admin_support: './frontend/www/js/omegaup/admin/support.js',
    admin_user: './frontend/www/js/omegaup/admin/user.js',
    admin_roles: './frontend/www/js/omegaup/admin/roles.js',
    arena_virtual: './frontend/www/js/omegaup/arena/virtual.js',
    coder_of_the_month: './frontend/www/js/omegaup/coderofthemonth/index.js',
    coder_of_the_month_notice: './frontend/www/js/omegaup/coderofthemonth/notice.js',
    contest_list: './frontend/www/js/omegaup/contest/list.js',
    contest_list_participant: './frontend/www/js/omegaup/contest/list_participant.js',
    contest_scoreboardmerge: './frontend/www/js/omegaup/contest/scoreboardmerge.js',
    course_edit: './frontend/www/js/omegaup/course/edit.js',
    course_intro: './frontend/www/js/omegaup/course/intro.js',
    course_new: './frontend/www/js/omegaup/course/new.js',
    course_student: './frontend/www/js/omegaup/course/student.js',
    course_students: './frontend/www/js/omegaup/course/students.js',
    group_list: './frontend/www/js/omegaup/group/list.js',
    problem_feedback: './frontend/www/js/omegaup/problem/feedback.js',
    problem_list: './frontend/www/js/omegaup/problem/list.js',
    schools_intro: './frontend/www/js/omegaup/schools/intro.js',
    schools_rank: './frontend/www/js/omegaup/schools/rank.js',
    qualitynomination_popup: './frontend/www/js/omegaup/arena/qualitynomination_popup.js',
    qualitynomination_list: './frontend/www/js/omegaup/qualitynomination/list.js',
    qualitynomination_demotionpopup:'./frontend/www/js/omegaup/arena/qualitynomination_demotionpopup.js',
    qualitynomination_details: './frontend/www/js/omegaup/qualitynomination/details.js',
    user_charts: './frontend/www/js/omegaup/user/charts.js',
    user_profile: './frontend/www/js/omegaup/user/profile.js',
    user_edit_email_form : './frontend/www/js/omegaup/user/emailedit.js',
    omegaup_styles: './frontend/www/sass/main.scss',
  },
  output: {
    path: path.resolve(__dirname, './frontend/www/'),
    publicPath: '/',
    filename: 'js/dist/[name].js',
    library: '[name]',
    libraryTarget: 'umd'
  },
  plugins: [
    new webpack.optimize.CommonsChunkPlugin({
      name: 'omegaup',
    }),
    new VueLoaderPlugin(),
    new ExtractTextPlugin({
      filename: 'css/dist/[name].css',
      allChunks: true,
    }),
    new RemoveSourceWebpackPlugin([omegaupStylesRegExp]),
  ],
  module: {
    rules: [
      {
        test: /\.vue$/,
        loader: 'vue-loader',
        options: {
          loaders: {
          }
          // other vue-loader options go here
        }
      },
      {
        test: /\.js$/,
        loader: 'babel-loader',
        exclude: /node_modules/
      },
      {
        test: /\.(png|jpg|gif|svg)$/,
        loader: 'file-loader',
        options: {
          name: '[name].[ext]?[hash]'
        }
      },
      {
        test: /\.css$/,
        loader: 'style-loader!css-loader',
      },
      {
        test: /\.scss$/,
        loader: ExtractTextPlugin.extract({
          fallback: 'style-loader',
          use: [
            'css-loader',
            'sass-loader'
          ],
        })
      }
    ],
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.common.js',
			'vue-async-computed': 'vue-async-computed/dist/vue-async-computed.js',
			jszip: 'jszip/dist/jszip.js',
    }
  },
  devServer: {
    historyApiFallback: true,
    noInfo: true
  },
  performance: {
    hints: false
  },
  devtool: '#cheap-source-map'
}

if (process.env.NODE_ENV === 'production') {
  module.exports.devtool = '#source-map'
  // http://vue-loader.vuejs.org/en/workflow/production.html
  module.exports.plugins = (module.exports.plugins || []).concat([
    new webpack.DefinePlugin({
      'process.env': {
        NODE_ENV: '"production"'
      }
    }),
    new webpack.optimize.UglifyJsPlugin({
      sourceMap: true
    }),
    new webpack.LoaderOptionsPlugin({
      minimize: true
    })
  ])
}
