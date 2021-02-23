import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_Profile from './Profilev2.vue';

describe('Profilev2.vue', () => {
  it('Should display profile edit button', () => {
    const badge_alias = 'contestManager';
    const badges = ['100SolvedProblems'];
    const wrapper = shallowMount(user_Profile, {
      propsData: {
        profile: {
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
        },
        data: {
          badges: badges,
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
          currentUsername: 'omegaup',
          gravatarURL51:
            'https://secure.gravatar.com/avatar/307aeed2f8a75f6fe407411671e3ca87?s=51',
          inContest: false,
          isAdmin: true,
          isLoggedIn: true,
          isMainUserIdentity: true,
          ownedBadges: [],
          programmingLanguages: {},
          userClassname: 'user-rank-unranked',
        } as types.ExtraProfileDetails,
        profileBadges: new Set(badge_alias) as Set<string>,
        visitorBadges: new Set(badge_alias) as Set<string>,
      },
    });
    expect(wrapper.find('a[href="/profile/edit/"]').exists()).toBe(true);
  });
});
