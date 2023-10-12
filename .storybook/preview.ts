/** @type { import('@storybook/vue').Preview } */
import '../frontend/www/third_party/bootstrap-4.5.0/css/bootstrap.min.css';

const preview = {
  parameters: {
    actions: { argTypesRegex: '^on[A-Z].*' },
    controls: {
      matchers: {
        color: /(background|color)$/i,
        date: /Date$/,
      },
    },
  },
};

export default preview;
