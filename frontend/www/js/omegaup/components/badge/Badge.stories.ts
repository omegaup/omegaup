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
  title: 'Components/Badge',
  argTypes: {
    // Story controls flatten nested badge fields for the controls panel.
    badge_alias: {
      control: 'select',
      options: AvailableBadges,
    },
    unlocked: {
      control: 'boolean',
    },
  },
};

export default meta;

type Story = StoryObj<typeof meta>;

export const Default: Story = {
  args: {
    badge_alias: '100solvedProblems',
    unlocked: true,
  },
  render: (args, { argTypes }) => ({
    components: { Badge },
    // bind props to badge object and injest as props
    props: Object.keys(argTypes),
    template: '<badge :badge="$props" />',
  }),
};

Default.storyName = 'Badges';
