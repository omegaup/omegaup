const ForkTsCheckerWebpackPlugin = require('fork-ts-checker-webpack-plugin');
const VueLoaderPlugin = require('vue-loader/lib/plugin');

const frontendConfig = require('./webpack.config-frontend.js');

frontendConfig.mode = 'development';
frontendConfig.target = 'node';
frontendConfig.devtool = 'inline-cheap-module-source-map';
delete frontendConfig.entry;
delete frontendConfig.optimization;
frontendConfig.plugins = frontendConfig.plugins.filter(
    plugin => (plugin instanceof ForkTsCheckerWebpackPlugin) ||
        (plugin instanceof VueLoaderPlugin));

module.exports = frontendConfig;
