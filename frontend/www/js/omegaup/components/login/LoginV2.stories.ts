import { Meta, StoryObj } from '@storybook/vue';
import LoginV2 from './LoginV2.vue';

const meta: Meta<typeof LoginV2> = {
  component: LoginV2,
  title: 'Components/Login/LoginV2',
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
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    githubClientId: 'dummy-github-client-id',
    githubState: 'dummy-github-state',
    googleClientId: 'dummy-google-client-id',
    activeTab: 'login',
  },
};

export const WithoutGithub: Story = {
  args: {
    githubClientId: '',
    githubState: null,
    googleClientId: 'dummy-google-client-id',
    activeTab: 'login',
  },
};