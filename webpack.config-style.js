const path = require('path');

const ExtractTextPlugin = require('extract-text-webpack-plugin');
const RemoveSourceWebpackPlugin = require('remove-source-webpack-plugin');

const omegaupStylesRegExp = /omegaup_styles\.js/;

module.exports = {
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
};
