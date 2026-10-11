import T from '../../lang';
import { getExternalUrl } from '../../urlHelper';

export interface NavbarAccess {
  isLoggedIn: boolean;
  isMainUserIdentity: boolean;
  isUnder13User: boolean;
  isReviewer: boolean;
}

export interface NavbarMenuEntry {
  divider?: boolean;
  title?: string;
  description?: string;
  icon?: [string, string];
  href?: string;
  target?: string;
  rel?: string | null;
  dataAttr?: string;
  visible?: (access: NavbarAccess) => boolean;
}

export function visibleEntries(
  entries: NavbarMenuEntry[],
  access: NavbarAccess,
): NavbarMenuEntry[] {
  return entries.filter((entry) => !entry.visible || entry.visible(access));
}

export const contestsMenuEntries: NavbarMenuEntry[] = [
  {
    title: T.navViewContests,
    description: T.navViewContestsDesc,
    icon: ['fas', 'trophy'],
    href: '/arena/',
    dataAttr: 'data-nav-contests-arena',
  },
  {
    title: T.contestsJoinScoreboards,
    description: T.contestsJoinScoreboardsDesc,
    icon: ['fas', 'list-ol'],
    href: '/scoreboardmerge/',
    visible: (access) => access.isMainUserIdentity,
  },
  {
    title: T.contestsCreate,
    description: T.contestsCreateDesc,
    icon: ['fas', 'flag-checkered'],
    href: '/contest/new/',
    dataAttr: 'data-nav-contests-create',
    visible: (access) => access.isMainUserIdentity && !access.isUnder13User,
  },
];

export const coursesMenuEntries: NavbarMenuEntry[] = [
  {
    title: T.navViewCourses,
    description: T.navViewCoursesDesc,
    icon: ['fas', 'graduation-cap'],
    href: '/course/',
    dataAttr: 'data-nav-courses-all',
  },
  {
    title: T.courseCreate,
    description: T.courseCreateDesc,
    icon: ['fas', 'chalkboard-teacher'],
    href: '/course/new/',
    dataAttr: 'data-nav-courses-create',
    visible: (access) => access.isMainUserIdentity && !access.isUnder13User,
  },
];

export const problemsMenuEntries: NavbarMenuEntry[] = [
  {
    title: T.navViewProblems,
    description: T.navViewProblemsDesc,
    icon: ['fas', 'layer-group'],
    href: '/problem/collection/',
    dataAttr: 'data-nav-problems-collection',
  },
  {
    title: T.navViewProblemsAll,
    description: T.navViewProblemsAllDesc,
    icon: ['fas', 'list'],
    href: '/problem/',
    dataAttr: 'data-nav-problems-list',
  },
  {
    title: T.bookmarkedProblems,
    description: T.bookmarkedProblemsDesc,
    icon: ['fas', 'bookmark'],
    href: '/profile/#problems',
  },
  { divider: true },
  {
    title: T.navViewLatestSubmissions,
    description: T.navViewLatestSubmissionsDesc,
    icon: ['fas', 'history'],
    href: '/submissions/',
  },
  {
    title: T.createZipFileForProblem,
    description: T.createZipFileForProblemDesc,
    icon: ['fas', 'plus-circle'],
    href: '/problem/creator/',
    visible: (access) => !access.isLoggedIn,
  },
  {
    title: T.navCreateProblemFromScratch,
    description: T.navCreateProblemFromScratchDesc,
    icon: ['fas', 'plus-circle'],
    href: '/problem/creator/',
    visible: (access) => access.isLoggedIn,
  },
  {
    title: T.navCreateProblemFromZip,
    description: T.navCreateProblemFromZipDesc,
    icon: ['fas', 'file-archive'],
    href: '/problem/new/',
    dataAttr: 'data-nav-problems-create',
    visible: (access) => access.isLoggedIn,
  },
  {
    title: T.navQualityNominationQueue,
    description: T.navQualityNominationQueueDesc,
    icon: ['fas', 'clipboard-check'],
    href: '/nomination/',
    visible: (access) => access.isReviewer,
  },
];

export const rankingMenuEntries: NavbarMenuEntry[] = [
  {
    title: T.navUserRanking,
    description: T.navUserRankingDesc,
    icon: ['fas', 'chart-line'],
    href: '/rank/',
  },
  {
    title: T.navCompareUsers,
    description: T.navCompareUsersDesc,
    icon: ['fas', 'balance-scale'],
    href: '/rank/compare/',
  },
  {
    title: T.navAuthorRanking,
    description: T.navAuthorRankingDesc,
    icon: ['fas', 'pen-nib'],
    href: '/rank/authors/',
  },
  {
    title: T.navSchoolRanking,
    description: T.navSchoolRankingDesc,
    icon: ['fas', 'school'],
    href: '/rank/schools/',
  },
  {
    title: T.navCoderOfTheMonth,
    description: T.navCoderOfTheMonthDesc,
    icon: ['fas', 'medal'],
    href: '/coderofthemonth/',
  },
  {
    title: T.navCoderOfTheMonthFemale,
    description: T.navCoderOfTheMonthFemaleDesc,
    icon: ['fas', 'medal'],
    href: '/coderofthemonth/female/',
  },
  {
    title: T.navSchoolOfTheMonth,
    description: T.navSchoolOfTheMonthDesc,
    icon: ['fas', 'award'],
    href: '/schoolofthemonth/',
  },
];

export const helpMenuEntries: NavbarMenuEntry[] = [
  {
    title: T.navTutorials,
    description: T.navTutorialsDesc,
    icon: ['fas', 'video'],
    href: getExternalUrl('YouTubeTutorialsURL'),
    target: '_blank',
  },
  {
    title: T.navDiscord,
    description: T.navDiscordDesc,
    icon: ['fas', 'comments'],
    href: getExternalUrl('DiscordInviteURL'),
    target: '_blank',
  },
  {
    title: T.navBlog,
    description: T.navBlogDesc,
    icon: ['fas', 'newspaper'],
    href: getExternalUrl('OmegaUpBlogURL'),
    target: '_blank',
  },
  { divider: true },
  {
    title: T.navProblemStatementEditor,
    description: T.navProblemStatementEditorDesc,
    icon: ['fas', 'pen'],
    href: '/problem/statement/',
    target: '_blank',
    rel: 'noopener noreferrer',
  },
  {
    title: T.navOmegaUpIDE,
    description: T.navOmegaUpIDEDesc,
    icon: ['fas', 'code'],
    href: '/grader/ephemeral/',
    target: '_blank',
    rel: 'noopener noreferrer',
  },
  {
    title: T.navKarel,
    description: T.navKarelDesc,
    icon: ['fas', 'robot'],
    href: '/karel.js/',
    target: '_blank',
    rel: 'noopener noreferrer',
  },
  { divider: true },
  {
    title: T.navAlgorithmsBook,
    description: T.navAlgorithmsBookDesc,
    icon: ['fas', 'book'],
    href: getExternalUrl('AlgorithmsBookURL'),
    target: '_blank',
  },
  {
    title: T.navCompetitiveProgrammingDataStructuresBook,
    description: T.navCompetitiveProgrammingDataStructuresBookDesc,
    icon: ['fas', 'database'],
    href: getExternalUrl('CompetitiveProgrammingBookURL'),
    target: '_blank',
    rel: 'noopener noreferrer',
  },
];
