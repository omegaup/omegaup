const path = require('path');

const HtmlWebpackPlugin = require('html-webpack-plugin');

const frontendConfig = require('./webpack.config-frontend.js');

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

const config = [
  frontendConfig,
  require('./webpack.config-style.js'),
  require('./webpack.config-grader.js'),
];

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
