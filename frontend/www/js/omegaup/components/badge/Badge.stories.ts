import Badge from './Badge.vue';
import { StoryObj, Meta } from '@storybook/vue';

const AvailableBadges = [
  '100solvedProblems',
  'contestManager',
  'virtualContestManager',
  'introToAlgorithmsCourseGraduate',
  'introToAlgorithms2CourseGraduate',
  'cppCourseGraduate',
  'pythonCourseGraduate',
  'coderOfTheMonth',
  'problemSetter',
  'cppExpert',
  'javaExpert',
  'karelExpert',
  'pascalExpert',
  'pythonExpert',
  'updatedUser',
  'problemOfTheWeekWithOmegaUp',
  'christmasProblem2021',
  'feedbackProvider',
  '500score',
  'legacyUser',
];

const meta: Meta<typeof Badge> = {
  component: Badge,
};

export default meta;

type Story = StoryObj<typeof Badge>;

export const Default: Story = {
  argTypes: {
    badge_alias: {
      control: 'select',
      options: AvailableBadges,
    },
    unlocked: {
      control: 'boolean',
    },
  },
  args: {
    unlocked: true,
    badge_alias: '100solvedProblems',
  },
  render: (args) => ({
    components: { Badge },
    // bind props to badge object and injest as props
    props: Object.keys(args),
    template: '<Badge :badge="$props" />',
  }),
};

Default.storyName = 'Badges';
