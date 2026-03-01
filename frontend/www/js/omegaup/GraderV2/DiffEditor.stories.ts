import { StoryObj, Meta } from '@storybook/vue';
import DiffEditorV2 from './DiffEditorV2.vue';

const meta: Meta<typeof DiffEditorV2> = {
  title: 'Grader/DiffEditor',
  component: DiffEditorV2,
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    storeMapping: {
      originalContents: 'original',
      modifiedContents: 'modified',
    },
    readOnly: false,
  },
  render: (args, { argTypes }) => ({
    components: { DiffEditorV2 },
    props: Object.keys(argTypes),
    template: '<DiffEditorV2 v-bind="$props" />',
  }),
};

export const ReadOnly: Story = {
  args: {
    storeMapping: {
      originalContents: 'original',
      modifiedContents: 'modified',
    },
    readOnly: true,
  },
  render: (args, { argTypes }) => ({
    components: { DiffEditorV2 },
    props: Object.keys(argTypes),
    template: '<DiffEditorV2 v-bind="$props" />',
  }),
};
