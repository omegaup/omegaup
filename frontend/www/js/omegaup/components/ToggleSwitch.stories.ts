import { StoryObj, Meta } from '@storybook/vue';
import ToggleSwitch, { ToggleSwitchSize } from './ToggleSwitch.vue';

const meta: Meta<typeof ToggleSwitch> = {
  component: ToggleSwitch,
  title: 'Components/ToggleSwitch',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated, so we can't get prop types from the component
    textDescription: {
      control: 'text',
    },
    checkedValue: {
      control: 'boolean',
    },
    size: {
      control: 'select',
      options: ToggleSwitchSize,
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    textDescription: 'Text for the check',
    checkedValue: false,
    size: ToggleSwitchSize.Large,
  },
  render: (args, { argTypes }) => ({
    components: { ToggleSwitch },
    props: Object.keys(argTypes),
    template:
      '<toggle-switch :text-description="$props.textDescription" :checked-value="$props.checkedValue" :size="$props.size" />',
  }),
};

Default.storyName = 'ToggleSwitch';
