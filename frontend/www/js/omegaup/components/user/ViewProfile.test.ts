import { shallowMount } from '@vue/test-utils';
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
  createdProblems: [],
  solvedProblems: [],
  stats: [],
  unsolvedProblems: [],
  ownedBadges: [],
  hasPassword: true,
};

describe('Profilev2.vue', () => {
  it('Should display navtab', () => {
    const badge_alias = 'contestManager';
    const wrapper = shallowMount(user_ViewProfile, {
      propsData: {
        profile,
        data,
        profileBadges: new Set(badge_alias) as Set<string>,
        visitorBadges: new Set(badge_alias) as Set<string>,
      },
    });
    expect(wrapper.find('[data-profile-navtabs]').exists()).toBe(true);
  });
});
