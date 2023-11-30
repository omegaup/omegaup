import { StoryObj, Meta } from '@storybook/vue';
import RadioSwitch from './RadioSwitch.vue';

const meta: Meta<typeof RadioSwitch> = {
  component: RadioSwitch,
  title: 'Components/RadioSwitch',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated, so we can't get prop types from the component
    name: {
      control: 'text',
    },
    selectedValue: {
      control: 'boolean',
    },
    valueForTrue: {
      control: 'boolean',
    },
    valueForFalse: {
      control: 'boolean',
    },
    textForTrue: {
      control: 'text',
    },
    textForFalse: {
      control: 'text',
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    name: 'name',
    selectedValue: true,
    valueForTrue: true,
    valueForFalse: false,
    textForTrue: 'Yes',
    textForFalse: 'No',
  },
  render: (args, { argTypes }) => ({
    components: { RadioSwitch },
    props: Object.keys(argTypes),
    template:
      '<radio-switch :name="$props.name" :text-for-true="$props.textForTrue" :text-for-false="$props.textForFalse" />',
  }),
};

Default.storyName = 'RadioSwitch';
