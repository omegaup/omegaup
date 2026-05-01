import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';

import user_SidebarMainInfo, { urlMapping } from './SidebarMainInfo.vue';

const profile: types.UserProfileInfo = {
  country: 'Mexico',
  country_id: 'MX',
  classname: 'user-rank-master',
  username: 'omegaup',
  hide_problem_tags: false,
  is_private: false,
  preferred_language: 'py2',
  programming_languages: {
    py2: 'python2',
  },
  rankinfo: {
    name: 'Test',
    problems_solved: 2,
    rank: 0,
  },
  is_own_profile: true,
  gravatar_92:
    'https://secure.gravatar.com/avatar/307aeed2f8a75f6fe407411671e3ca87?s=51',
};

const data: types.ExtraProfileDetails = {
  badges: [],
  contests: {},
  createdProblems: [],
  createdContests: [],
  createdCourses: [],
  ownedBadges: [],
  bookmarkedProblems: [],
  solvedProblems: [
    {
      accepted: 1,
      alias: 'alias1',
      difficulty: 0,
      submissions: 2,
      title: 'title',
      quality_seal: false,
    },
    {
      accepted: 1,
      alias: 'alias2',
      difficulty: 1,
      submissions: 3,
      title: 'title2',
      quality_seal: false,
    },
    {
      accepted: 1,
      alias: 'alias3',
      difficulty: 2,
      submissions: 5,
      title: 'title3',
      quality_seal: false,
    },
  ],
  stats: [],
  unsolvedProblems: [],
  hasPassword: true,
};

const rankingMapping: { classname: string; rank: string }[] = [
  { classname: 'user-rank-beginner', rank: T.profileRankBeginner },
  { classname: 'user-rank-specialist', rank: T.profileRankSpecialist },
  { classname: 'user-rank-expert', rank: T.profileRankExpert },
  { classname: 'user-rank-master', rank: T.profileRankMaster },
  {
    classname: 'user-rank-international-master',
    rank: T.profileRankInternationalMaster,
  },
];

describe('SidebarMainInfo.vue', () => {
  it('Should display visible buttons', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });
    for (const url of urlMapping.filter(
      (url: { key: string; title: string; visible: boolean }) => url.visible,
    )) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeTruthy();
      expect(wrapper.find(urlSelector).text()).toBe(url.title);
    }
    for (const url of urlMapping.filter(
      (url: { key: string; title: string; visible: boolean }) => !url.visible,
    )) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeFalsy();
    }
  });

  it('Should display number of solved problems', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: { profile, data },
    });
    expect(wrapper.find('div[data-solved-problems]>h4').text()).toBe('3');
  });

  it('Should not display buttons for a different user profile', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile: { ...profile, ...{ is_own_profile: false } },
        data,
      },
    });

    for (const url of urlMapping) {
      const urlSelector = `a[href="/profile/#${url.key}"]`;
      expect(wrapper.find(urlSelector).exists()).toBeFalsy();
    }
  });

  it('Should display Add password button when user does not have password', () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile,
        data: { ...data, ...{ hasPassword: false } },
      },
    });

    expect(
      wrapper.find('a[href="/profile/#add-password"]').exists(),
    ).toBeTruthy();
    expect(
      wrapper.find('a[href="/profile/#change-password"]').exists(),
    ).toBeFalsy();
  });
});

describe.each(rankingMapping)(`A user:`, (rank) => {
  it(`whose classname is ${rank.classname} should have rank ${rank.rank}`, () => {
    const wrapper = shallowMount(user_SidebarMainInfo, {
      propsData: {
        profile: { ...profile, ...{ classname: rank.classname } },
        data,
      },
    });
    expect(wrapper.text()).toContain(rank.rank);
  });
});
