import Badge from './Badge.vue';

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

export const SolvedProblems = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[0],
    },
  },
};

export const ContestManager = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[1],
    },
  },
};

export const VirtualContestManager = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[2],
    },
  },
};

export const IntroToAlgorithmsCourseGraduate = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[3],
    },
  },
};

export const IntroToAlgorithms2CourseGraduate = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[4],
    },
  },
};

export const CppCourseGraduate = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[5],
    },
  },
};

export const PythonCourseGraduate = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[6],
    },
  },
};

export const CoderOfTheMonth = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[7],
    },
  },
};

export const ProblemSetter = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[8],
    },
  },
};

export const CppExpert = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[9],
    },
  },
};

export const JavaExpert = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[10],
    },
  },
};

export const KarelExpert = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[11],
    },
  },
};

export const PascalExpert = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[12],
    },
  },
};

export const PythonExpert = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[13],
    },
  },
};

export const UpdatedUser = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[14],
    },
  },
};

export const ProblemOfTheWeekWithOmegaUp = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[15],
    },
  },
};

export const ChristmasProblem2021 = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[16],
    },
  },
};

export const FeedbackProvider = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[17],
    },
  },
};

export const Score500 = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[18],
    },
  },
};

export const LegacyUser = {
  args: {
    badge: {
      ...BaseBadgeProps,
      badge_alias: AvailableBadges[19],
    },
  },
};