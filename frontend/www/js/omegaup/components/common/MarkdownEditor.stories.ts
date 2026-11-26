import { Meta, StoryObj } from '@storybook/vue';
import MarkdownEditor from './MarkdownEditor.vue';

const meta: Meta<typeof MarkdownEditor> = {
  component: MarkdownEditor,
  title: 'Components/Common/MarkdownEditor',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    value: '',
    idPostfix: '-default',
  },
};
Default.storyName = 'Empty editor';

export const WithContent: Story = {
  args: {
    value: '# Title\n\nSome **bold** and *italic* text.\n\n- First\n- Second',
    idPostfix: '-with-content',
  },
};
WithContent.storyName = 'With Markdown content';

export const ReadOnly: Story = {
  args: {
    value: 'This content **cannot** be edited.',
    idPostfix: '-read-only',
    readOnly: true,
  },
};
ReadOnly.storyName = 'Read only';

export const Invalid: Story = {
  args: {
    value: '',
    idPostfix: '-invalid',
    invalid: true,
    required: true,
  },
};
Invalid.storyName = 'Invalid state';

export const WithMaxLength: Story = {
  args: {
    value: 'Limited to 50 characters.',
    idPostfix: '-max-length',
    maxlength: 50,
  },
};
WithMaxLength.storyName = 'With maxlength';

export const MultipleEditors: Story = {
  render: () => ({
    components: { MarkdownEditor },
    data() {
      return { first: 'First **editor**', second: 'Second *editor*' };
    },
    template: `
      <div>
        <MarkdownEditor v-model="first" id-postfix="-first" class="mb-4" />
        <MarkdownEditor v-model="second" id-postfix="-second" />
      </div>
    `,
  }),
};
MultipleEditors.storyName = 'Multiple instances';