import Badge from './Badge.vue';

const BaseBadgeProps = {
  assignation_time: new Date(),
  badge_alias: '100solvedProblems',
  first_assignation: new Date(),
  owners_count: 10,
  total_users: 100,
};

export default {
  title: 'components/Badge',
  component: Badge,
  tags: ['autodocs'],
  render: (args: any, { argTypes }: { argTypes: any }) => ({
    props: Object.keys(argTypes),
    components: { Badge },
    template: '<badge :badge="badge" />',
  }),
  argTypes: {},
};

export const Locked = {
  args: {
    badge: {
        ...BaseBadgeProps,
    },
  },
};

export const Unlocked = {
    args: {
        badge: {
            ...BaseBadgeProps,
            unlocked: true,
        },
    },
};
