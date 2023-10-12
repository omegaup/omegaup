import type { StorybookConfig } from '@storybook/vue-webpack5';

const config: StorybookConfig = {
  stories: [
    '../frontend/www/js/omegaup/components/**/*.stories.mdx',
    '../frontend/www/js/omegaup/components/**/*.stories.@(vue|js|jsx|ts|tsx)',
  ],
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
};
export default config;
