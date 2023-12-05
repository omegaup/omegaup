import { StoryObj, Meta } from '@storybook/vue';
import Markdown from './Markdown.vue';

const meta: Meta<typeof Markdown> = {
  component: Markdown,
  title: 'Components/Markdown',
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    markdown: `<span>**bold**</span>`,
  },
  render: (args) => ({
    components: { Markdown },
    props: Object.keys(args),
    template: '<markdown :markdown="$props.markdown" />',
  }),
};

Default.storyName = 'Markdown';
