import { Meta, StoryObj } from '@storybook/vue';
import EmptyState from './EmptyState.vue';

const meta: Meta<typeof EmptyState> = {
  component: EmptyState,
  title: 'Components/Common/EmptyState',
  argTypes: {
    // eslint-disable-next-line @typescript-eslint/ban-ts-comment
    // @ts-ignore FIXME: vue-property-decorator is deprecated
    icon: {
      control: 'text',
      description: 'FontAwesome icon name (e.g. "users", "graduation-cap")',
    },
    title: {
      control: 'text',
      description: 'Main header text',
    },
    description: {
      control: 'text',
      description: 'Secondary contextual explanation',
    },
    buttonText: {
      control: 'text',
      description: 'Primary action button label',
    },
    buttonLink: {
      control: 'text',
      description: 'Optional URL for link-style primary action',
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    icon: 'clipboard-list',
    title: 'No items found',
    description: 'There are currently no items to display in this list.',
  },
};

export const WithButtonLink: Story = {
  args: {
    icon: 'users',
    title: 'No team groups created',
    description: 'Create your first team group to start organizing contestants.',
    buttonText: 'Create Team Group',
    buttonLink: '/group/new/',
  },
};

export const WithActionButton: Story = {
  args: {
    icon: 'terminal',
    title: 'No runs found',
    description: 'There are no active code executions for this problem.',
    buttonText: 'Re-run Submissions',
  },
};

export const CustomActionSlot: Story = {
  args: {
    icon: 'graduation-cap',
    title: 'No courses available',
    description: 'Join a course or create a new course for your students.',
  },
  render: (args) => ({
    components: { EmptyState },
    props: Object.keys(args),
    template: `
      <empty-state v-bind="$props">
        <template #action>
          <div class="d-flex justify-content-center gap-2">
            <button class="btn btn-primary mr-2">Create Course</button>
            <button class="btn btn-secondary">Join Course</button>
          </div>
        </template>
      </empty-state>
    `,
  }),
};
