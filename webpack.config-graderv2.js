const path = require('path');

const MonacoWebpackPlugin = require('monaco-editor-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');
const HtmlWebpackPlugin = require('html-webpack-plugin');
const { CleanWebpackPlugin } = require('clean-webpack-plugin');

module.exports = {
  name: 'grader',
  entry: {
    grader_ephemeral: [
      '@babel/polyfill',
      './frontend/www/js/omegaup/graderv2/ephemeral.ts',
    ],
  },
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
      {
        test: /\.ts$/,
        loader: 'ts-loader',
        options: {
          appendTsSuffixTo: [/\.vue$/],
          transpileOnly: true,
        },
      },
      {
        test: /\.scss$/,
        use: ['vue-style-loader', 'css-loader', 'sass-loader'],
      },
    ],
  },
  optimization: {
    realContentHash: true,
  },
  resolve: {
    extensions: ['.ts', '.js', '.vue'],
    alias: {
      vue$: 'vue/dist/vue.common.js',
      'vue-async-computed': 'vue-async-computed/dist/vue-async-computed.js',
    },
    fallback: {
      buffer: require.resolve('buffer/'),
      stream: require.resolve('stream-browserify'),
    },
  },
  plugins: [
    new VueLoaderPlugin(),
    new MonacoWebpackPlugin(),
    new HtmlWebpackPlugin({
      template: path.resolve(
        __dirname,
        'frontend/www/grader/ephemeral/templates',
        'index-light.html',
      ),
      filename: path.resolve(
        __dirname,
        'frontend/www/grader/ephemeral',
        'index-light.html',
      ),
      scriptLoading: 'defer',
    }),
    new HtmlWebpackPlugin({
      template: path.resolve(
        __dirname,
        'frontend/www/grader/ephemeral/templates',
        'index.html',
      ),
      filename: path.resolve(
        __dirname,
        'frontend/www/grader/ephemeral',
        'index.html',
      ),
      scriptLoading: 'defer',
    }),
    new CleanWebpackPlugin({
      verbose: true,
      dry: false,
      cleanOnceBeforeBuildPatterns: ['grader_ephemeral-*'],
      dangerouslyAllowCleanPatternsOutsideProject: true,
    }),
  ],
  output: {
    path: path.resolve(__dirname, './frontend/www/js/dist/'),
    publicPath: '/js/dist/',
    filename: '[name]-[contenthash].js',
    library: '[name]',
    libraryTarget: 'umd',
  },
};
