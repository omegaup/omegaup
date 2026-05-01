import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';

import user_MainInfo from './MainInfo.vue';

const propsData = {
  profile: {
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
  },
  data: {
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
  } as types.ExtraProfileDetails,
  edit: false,
};

describe('MainInfo.vue', () => {
  it('Should display profile edit button', () => {
    const wrapper = shallowMount(user_MainInfo, {
      propsData,
    });
    expect(
      wrapper.find('a[href="/profile/#edit-basic-information"]').exists(),
    ).toBe(true);
    expect(
      wrapper.find('a[href="/profile/#edit-basic-information"]').text(),
    ).toBe(T.profileEdit);
  });

  it('Should display profile see button', () => {
    propsData.edit = true;
    const wrapper = shallowMount(user_MainInfo, {
      propsData,
    });
    expect(wrapper.find('a[href="/profile/"]').exists()).toBe(true);
    expect(wrapper.find('a[href="/profile/"]').text()).toBe(
      T.userEditViewProfile,
    );
  });

  it('Should display number of solved problems', () => {
    const wrapper = shallowMount(user_MainInfo, {
      propsData,
    });
    expect(wrapper.text()).toContain('3');
  });

  it('Should display correct rank', () => {
    const wrapper = shallowMount(user_MainInfo, {
      propsData,
    });
    expect(wrapper.text()).toContain(T.profileRankMaster);
  });
});
