import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';

import user_NavbarMainInfo, { urlMapping } from './NavbarMainInfo.vue';

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
  ownedBadges: [],
  solvedProblems: [
    {
      accepted: 1,
      alias: 'alias1',
      difficulty: 0,
      submissions: 2,
      title: 'title',
    },
    {
      accepted: 1,
      alias: 'alias2',
      difficulty: 1,
      submissions: 3,
      title: 'title2',
    },
    {
      accepted: 1,
      alias: 'alias3',
      difficulty: 2,
      submissions: 5,
      title: 'title3',
    },
  ],
  stats: [],
  unsolvedProblems: [],
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

describe('NavbarMainInfo.vue', () => {
  it('Should display visible buttons', () => {
    const wrapper = shallowMount(user_NavbarMainInfo, {
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
    const wrapper = shallowMount(user_NavbarMainInfo, {
      propsData: { profile, data },
    });
    expect(wrapper.text()).toContain('3');
  });
});

describe.each(rankingMapping)(`A user:`, (rank) => {
  it(`whose classname is ${rank.classname} should have rank ${rank.rank}`, () => {
    const wrapper = shallowMount(user_NavbarMainInfo, {
      propsData: {
        profile: { ...profile, ...{ classname: rank.classname } },
        data,
      },
    });
    expect(wrapper.text()).toContain(rank.rank);
  });
});
