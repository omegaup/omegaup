import { Meta, StoryFn } from '@storybook/vue';
import TextEditorV2 from './TextEditorV2.vue';

export default {
  title: 'Grader/TextEditor',
  component: TextEditorV2,
  argTypes: {
    readOnly: { control: 'boolean' },
    extension: { control: 'text' },
    module: { control: 'text' },
  },
} as Meta<typeof TextEditorV2>;

const Template: StoryFn = (args, { argTypes }) => ({
  components: { TextEditorV2 },
  props: Object.keys(argTypes),
  template: `
    <div style="height: 400px; width: 100%; border: 1px solid #ccc; background: #f9fafb; padding: 20px;">
      <TextEditorV2 v-bind="$props" />
    </div>
  `,
});

export const Default = Template.bind({});
Default.args = {
  readOnly: false,
  extension: 'out',
  module: 'output',
  storeMapping: {
    contents: 'textEditorContents',
    module: 'textEditorModule',
  },
};

export const ReadOnly = Template.bind({});
ReadOnly.args = {
  ...Default.args,
  readOnly: true,
};

export const InputFile = Template.bind({});
InputFile.args = {
  ...Default.args,
  extension: 'in',
  module: 'sample',
};

export const ErrorLog = Template.bind({});
ErrorLog.args = {
  ...Default.args,
  extension: 'err',
  module: 'compile',
  readOnly: true,
};
