const path = require('path');
const webpack = require('webpack');

module.exports = {
  entry: './cmd/kareljs',
  module: {
    rules: [
      { test: /cmd\/kareljs$/, use: 'shebang-loader' },
      {
        test: /\.node$/,
        use: 'node-loader',
      },
      {
        test: /(\.js|cmd\/kareljs)$/,
        exclude: /(node_modules|bower_components)/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/preset-env'],
          },
        },
      },
    ],
  },
  output: {
    path: path.join(__dirname, '/dist/'),
    filename: 'karel.js',
    publicPath: './',
    sourceMapFilename: 'karel.map',
  },
  target: 'node',
  mode: 'development',
  plugins: [
    new webpack.BannerPlugin({
      banner: '#!/usr/bin/env node',
      raw: true,
      entryOnly: true,
    }),
  ],
};
