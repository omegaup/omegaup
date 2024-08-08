const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');

const frontendConfig = require('./webpack.config-frontend.js');
// The following lines of code are required as the current webpack version we are using is producing an error with the openssl library. They have removed a function that webpack is relying on, which is causing this error. These lines of code override the createHash method of the crypto module to avoid the error.
const crypto = require('crypto');
const crypto_orig_createHash = crypto.createHash;
crypto.createHash = (algorithm) =>
  crypto_orig_createHash(algorithm == 'md4' ? 'sha256' : algorithm);

// Generate the JSON dependency objects.
for (const entryname of Object.keys(frontendConfig.entry)) {
  frontendConfig.plugins.push(
    new HtmlWebpackPlugin({
      inject: false,
      chunks: [entryname],
      filename: `js/dist/${entryname}.deps.json`,
      template: path.resolve(__dirname, './stuff/webpack/deps.ejs'),
    }),
  );
}

const config = [frontendConfig, require('./webpack.config-style.js')];

module.exports = (env, argv) => {
  const devtool =
    argv && argv.mode !== 'development' ? 'source-map' : 'cheap-source-map';

  for (const entry of config) {
    Object.assign(entry, {
      devtool: devtool,
      devServer: { historyApiFallback: true, noInfo: true },
      performance: { hints: false },
      watchOptions: {
        aggregateTimeout: 300,
        poll: 1000,
        ignored: /node_modules/,
      },
    });
  }
  return config;
};
