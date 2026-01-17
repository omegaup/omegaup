/** @type { import('@storybook/vue-webpack5').StorybookConfig } */
const config = {
  stories: ['../frontend/www/js/omegaup/components/**/*.stories.@(js|jsx|ts|tsx)'],
  addons: [
    '@storybook/addon-links',
    '@storybook/addon-essentials',
    '@storybook/addon-interactions',
  ],
  framework: {
    name: '@storybook/vue-webpack5',
    options: {},
  },
  docs: {
    autodocs: 'tag',
  },
  staticDirs: ['../frontend/www'],
  webpackFinal: async (config) => {
    if (config.resolve) {
      config.resolve.extensions = ['.js', '.ts', '.vue', '.json'];
      config.resolve.alias = {
        ...config.resolve.alias,
        '@': require('path').resolve(__dirname, '../frontend/www/'),
      };
    }

    return {
      ...config,
      module: {
        ...config.module,
        rules: [
          ...config.module.rules,
          {
            test: /\.(png|jpg|gif|svg)$/,
            loader: 'file-loader',
            options: { name: '[name].[ext]?[hash]' },
          },
          {
            test: /\.css$/,
            use: ['vue-style-loader', 'css-loader'],
            exclude: [/node_modules/, /frontend\/www\/third_party/],
          },
          {
            test: /\.scss$/,
            use: ['vue-style-loader', 'css-loader', 'sass-loader']
          },
        ],
      },
    }
  },
};
export default config;
