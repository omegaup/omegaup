import { Meta, StoryFn } from '@storybook/vue';
import MonacoEditorV2 from './MonacoEditorV2.vue';

export default {
  title: 'Components/MonacoEditor',
  component: MonacoEditorV2,
  argTypes: {
    readOnly: { control: 'boolean' },
  },
} as Meta<typeof MonacoEditorV2>;

const Template: StoryFn = (args, { argTypes }) => ({
  components: { MonacoEditorV2 },
  props: Object.keys(argTypes),
  template: `
    <div style="height: 500px; width: 100%; border: 1px solid #ccc;">
      <MonacoEditorV2 v-bind="$props" />
    </div>
  `,
});

export const Default = Template.bind({});
Default.args = {
  readOnly: false,
  storeMapping: {
    contents: 'editorContents',
    language: 'editorLanguage',
    module: 'editorModule',
  },
};

export const ReadOnly = Template.bind({});
ReadOnly.args = {
  ...Default.args,
  readOnly: true,
};
