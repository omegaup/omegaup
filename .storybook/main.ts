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
  staticDirs: ['../frontend/www']
};
export default config;
