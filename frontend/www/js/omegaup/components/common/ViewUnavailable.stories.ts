import { Meta, StoryObj } from '@storybook/vue';
import ViewUnavailable from './ViewUnavailable.vue';

const meta: Meta<typeof ViewUnavailable> = {
  component: ViewUnavailable,
  title: 'Components/Common/ViewUnavailable',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated
    title: {
      control: 'text',
      description: 'The heading shown to the user',
    },
    description: {
      control: 'text',
      description: 'Optional detail explaining why the view is unavailable',
    },
    icon: {
      control: 'text',
      description: 'Font Awesome icon name',
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {},
};

export const WithDescription: Story = {
  args: {
    description: 'The EphemeralGrader IDE is currently disabled.',
  },
};
WithDescription.storyName = 'With description';

export const CustomMessage: Story = {
  args: {
    title: 'We are under maintenance',
    description: 'Please come back in a few minutes.',
    icon: 'tools',
  },
};
CustomMessage.storyName = 'Custom message and icon';
