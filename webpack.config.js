var path = require('path')
var webpack = require('webpack')

module.exports = {
  entry: {
    omegaup: './frontend/www/js/omegaup/omegaup.js',
    course_edit: ['babel-polyfill', './frontend/www/js/omegaup/course/edit.js'],
    course_new: './frontend/www/js/omegaup/course/new.js',
    course_student: './frontend/www/js/omegaup/course/student.js',
    course_students: ['babel-polyfill', './frontend/www/js/omegaup/course/students.js'],
    schools_intro: './frontend/www/js/omegaup/schools/intro.js',
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
