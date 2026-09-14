var webpack = require('webpack');

var PROD = JSON.parse(process.env.PROD_ENV || '0');

module.exports = {
  entry: './csv.js',
  devtool: 'source-map',
  output: {
    path: './',
    filename: 'csv.min.js'
  },
  plugins: [
    new webpack.optimize.UglifyJsPlugin({
      compress: { warnings: false }
    })
  ]
};