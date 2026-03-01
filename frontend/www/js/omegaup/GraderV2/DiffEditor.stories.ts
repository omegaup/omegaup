import DiffEditorV2 from './DiffEditorV2.vue';

export default {
  title: 'Grader/DiffEditor',
  component: DiffEditorV2,
};

const Template = (args) => ({
  components: { DiffEditorV2 },
  setup() {
    return { args };
  },
  template: '<DiffEditorV2 v-bind="args" />',
});

export const Default = Template.bind({});
Default.args = {
  storeMapping: { originalContents: 'original', modifiedContents: 'modified' },
  readOnly: false,
};

export const ReadOnly = Template.bind({});
ReadOnly.args = {
  storeMapping: { originalContents: 'original', modifiedContents: 'modified' },
  readOnly: true,
};
