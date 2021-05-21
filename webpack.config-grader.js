const path = require('path');

const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');
const { VueLoaderPlugin } = require('vue-loader');

module.exports = {
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
      },
      {
        test: /\.js$/,
        exclude: /node_modules/,
        use: {
          loader: 'babel-loader',
          options: {
            presets: ['@babel/env'],
            cacheDirectory: true,
          },
        },
      },
      {
        test: /\.css$/,
        use: ['style-loader', 'css-loader'],
      },
      {
        test: /\.ttf$/,
        use: ['file-loader'],
      },
    ],
  },
  resolve: {
    alias: {
      vue$: 'vue/dist/vue.common.js',
      'vue-async-computed': 'vue-async-computed/dist/vue-async-computed.js',
    },
    fallback: {
      buffer: require.resolve('buffer/'),
      stream: require.resolve('stream-browserify'),
    },
  },
  plugins: [new VueLoaderPlugin(), new MonacoWebpackPlugin()],
  output: {
    path: path.resolve(__dirname, './frontend/www/js/dist/'),
    publicPath: '/js/dist/',
    filename: '[name].js',
    library: '[name]',
    libraryTarget: 'umd',
  },
};
