const path = require('path');

const MiniCssExtractPlugin = require('mini-css-extract-plugin');
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
    new MiniCssExtractPlugin({
      filename: 'css/dist/[name].css',
    }),
    new RemoveSourceWebpackPlugin([omegaupStylesRegExp]),
  ],
  module: {
    rules: [
      {
        test: /\.scss$/,
        use: [MiniCssExtractPlugin.loader, 'css-loader', 'sass-loader'],
      },
    ],
  },
};
