import { mount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_ViewProfile from './ViewProfile.vue';

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  name: 'omegaUp admin',
  classname: 'user-rank-unranked',
  email: 'admin@omegaup.com',
  username: 'omegaup',
  verified: true,
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 1,
  },
  is_own_profile: true,
};

const createdContests: types.Contest[] = [
  {
    admission_mode: 'public',
    alias: 'contest-1',
    contest_id: 1,
    description: 'description',
    finish_time: new Date(),
    last_updated: new Date(),
    score_mode: 'all_or_nothing',
    problemset_id: 1,
    recommended: true,
    scoreboard_url: 'scoreboard_url',
    scoreboard_url_admin: 'scoreboard_url_admin',
    start_time: new Date(),
    title: 'contest 1',
  },
  {
    admission_mode: 'private',
    alias: 'contest-2',
    contest_id: 2,
    description: 'description',
    finish_time: new Date(),
    last_updated: new Date(),
    score_mode: 'all_or_nothing',
    problemset_id: 2,
    recommended: true,
    scoreboard_url: 'scoreboard_url',
    scoreboard_url_admin: 'scoreboard_url_admin',
    start_time: new Date(),
    title: 'contest 2',
  },
  {
    admission_mode: 'private',
    alias: 'contest-3',
    contest_id: 3,
    description: 'description',
    finish_time: new Date(),
    last_updated: new Date(),
    score_mode: 'all_or_nothing',
    problemset_id: 3,
    recommended: true,
    scoreboard_url: 'scoreboard_url',
    scoreboard_url_admin: 'scoreboard_url_admin',
    start_time: new Date(),
    title: 'contest 3',
  },
];

const createdCourses: types.Course[] = [
  {
    admission_mode: 'public',
    alias: 'course-1',
    archived: false,
    course_id: 1,
    description: 'description',
    name: 'course 1',
    needs_basic_information: false,
    requests_user_information: 'no',
    show_scoreboard: true,
    start_time: new Date(),
  },
  {
    admission_mode: 'private',
    alias: 'course-2',
    archived: false,
    course_id: 2,
    description: 'description',
    name: 'course 2',
    needs_basic_information: false,
    requests_user_information: 'no',
    show_scoreboard: true,
    start_time: new Date(),
  },
  {
    admission_mode: 'private',
    alias: 'course-3',
    archived: false,
    course_id: 3,
    description: 'description',
    name: 'course 3',
    needs_basic_information: false,
    requests_user_information: 'no',
    show_scoreboard: true,
    start_time: new Date(),
  },
];

const data: types.ExtraProfileDetails = {
  badges: ['100SolvedProblems'],
  contests: {
    prueba: {
      data: {
        alias: 'prueba',
        finish_time: new Date(),
        last_updated: new Date(),
        start_time: new Date(),
        title: 'prueba',
      },
      place: 1,
    },
  },
  createdContests: createdContests,
  createdCourses: createdCourses,
  createdProblems: [],
  solvedProblems: [],
  stats: [],
  unsolvedProblems: [],
  ownedBadges: [],
  bookmarkedProblems: [],
  hasPassword: true,
};

describe('Profilev2.vue', () => {
  it('Should display navtab', () => {
    const badgeAlias = 'contestManager';
    const wrapper = mount(user_ViewProfile, {
      propsData: {
        profile,
        data,
        profileBadges: new Set([badgeAlias]),
        visitorBadges: new Set([badgeAlias]),
      },
    });
    expect(wrapper.find('[data-profile-navtabs]').exists()).toBe(true);
  });

  it('Should display all contests', async () => {
    const badgeAlias = 'contestManager';
    const wrapper = mount(user_ViewProfile, {
      propsData: {
        profile,
        data,
        profileBadges: new Set([badgeAlias]),
        visitorBadges: new Set([badgeAlias]),
      },
    });
    await wrapper.find('a[data-created-content-tab]').trigger('click');
    for (const contest of createdContests) {
      expect(wrapper.find(`a[href="/arena/${contest.alias}/"]`)).toBeDefined();
    }
  });

  it('Should only display public contests', async () => {
    const badgeAlias = 'contestManager';
    const wrapper = mount(user_ViewProfile, {
      propsData: {
        profile: { ...profile, is_own_profile: false },
        data,
        profileBadges: new Set([badgeAlias]),
        visitorBadges: new Set([badgeAlias]),
      },
    });
    await wrapper.find('a[data-created-content-tab]').trigger('click');
    const publicContests = createdContests.filter(
      (contest) => contest.admission_mode === 'public',
    );
    const otherContests = createdContests.filter(
      (contest) => contest.admission_mode !== 'public',
    );
    for (const contest of publicContests) {
      expect(wrapper.find(`a[href="/arena/${contest.alias}/"]`)).toBeDefined();
    }
    for (const contest of otherContests) {
      expect(!wrapper.find(`a[href="/arena/${contest.alias}/"]`));
    }
  });

  it('Should display all Courses', async () => {
    const badgeAlias = 'contestManager';
    const wrapper = mount(user_ViewProfile, {
      propsData: {
        profile,
        data,
        profileBadges: new Set([badgeAlias]),
        visitorBadges: new Set([badgeAlias]),
      },
    });
    await wrapper.find('a[data-created-content-tab]').trigger('click');
    for (const course of createdCourses) {
      expect(wrapper.find(`a[href="/course/${course.alias}/"]`)).toBeDefined();
    }
  });

  it('Should only display public Courses', async () => {
    const badgeAlias = 'contestManager';
    const wrapper = mount(user_ViewProfile, {
      propsData: {
        profile: { ...profile, is_own_profile: false },
        data,
        profileBadges: new Set([badgeAlias]),
        visitorBadges: new Set([badgeAlias]),
      },
    });
    await wrapper.find('a[data-created-content-tab]').trigger('click');
    const publicCourses = createdCourses.filter(
      (course) => course.admission_mode === 'public',
    );
    const otherCourses = createdCourses.filter(
      (course) => course.admission_mode !== 'public',
    );
    for (const course of publicCourses) {
      expect(wrapper.find(`a[href="/course/${course.alias}/"]`)).toBeDefined();
    }
    for (const course of otherCourses) {
      expect(!wrapper.find(`a[href="/course/${course.alias}/"]`));
    }
  });
});
