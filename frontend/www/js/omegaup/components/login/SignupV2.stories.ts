import { Meta, StoryObj } from '@storybook/vue';
import SignupV2 from './SignupV2.vue';

const meta: Meta<typeof SignupV2> = {
  component: SignupV2,
  title: 'Components/Login/SignupV2',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated
    githubClientId: {
      control: 'text',
      description: 'GitHub OAuth Client ID',
    },
    githubState: {
      control: 'text',
      description: 'GitHub OAuth CSRF state parameter',
    },
    googleClientId: {
      control: 'text',
      description: 'Google OAuth Client ID',
    },
    activeTab: {
      control: 'radio',
      options: ['login', 'signup'],
      description: 'Currently active tab',
    },
    validateRecaptcha: {
      control: 'boolean',
      description: 'Whether to show recaptcha',
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    githubClientId: 'dummy-github-client-id',
    githubState: 'dummy-github-state',
    googleClientId: 'dummy-google-client-id',
    activeTab: 'signup',
    validateRecaptcha: false,
  },
};

export const WithoutGithub: Story = {
  args: {
    githubClientId: '',
    githubState: null,
    googleClientId: 'dummy-google-client-id',
    activeTab: 'signup',
    validateRecaptcha: false,
  },
};

export const LoginTabActive: Story = {
  args: {
    githubClientId: 'dummy-github-client-id',
    githubState: 'dummy-github-state',
    googleClientId: 'dummy-google-client-id',
    activeTab: 'login',
    validateRecaptcha: false,
  },
};
