var path = require('path')
var webpack = require('webpack')

module.exports = {
  entry: {
    omegaup: ['babel-polyfill', './frontend/www/js/omegaup/omegaup.js'],
    admin_user: './frontend/www/js/omegaup/admin/user.js',
    contest_list: './frontend/www/js/omegaup/contest/list.js',
    course_edit: './frontend/www/js/omegaup/course/edit.js',
    course_new: './frontend/www/js/omegaup/course/new.js',
    course_student: './frontend/www/js/omegaup/course/student.js',
    course_students: './frontend/www/js/omegaup/course/students.js',
    group_list: './frontend/www/js/omegaup/group/list.js',
    schools_intro: './frontend/www/js/omegaup/schools/intro.js',
    schools_rank: './frontend/www/js/omegaup/schools/rank.js',
    arena_quality_nom: './frontend/www/js/omegaup/arena/quality_nom.js',
  },
  output: {
    path: path.resolve(__dirname, './frontend/www/js/dist'),
    publicPath: '/js/dist/',
    filename: '[name].js',
    library: '[name]',
    libraryTarget: 'umd'
  },
  plugins: [
    new webpack.optimize.CommonsChunkPlugin({
      name: 'omegaup',
    }),
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
      }
    ]
  },
  resolve: {
    alias: {
      'vue$': 'vue/dist/vue.common.js',
			'vue-async-computed': 'vue-async-computed/dist/index.js',
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
