import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import expect from 'expect';
import user_Profile from './Profile.vue';

describe('Profile.vue', () => {
  it('Should display Profile', () => {
    const badge_alias = 'contestManager';
    const badges = ['100SolvedProblems'];
    const wrapper = shallowMount(user_Profile, {
      propsData: {
        data: {
          badges: badges,
          bootstrap4: false,
          contests: [],
          createdProblems: [],
          currentUsername: 'omegaup',
          gravatarURL51:
            'https://secure.gravatar.com/avatar/307aeed2f8a75f6fe407411671e3ca87?s=51',
          inContest: false,
          isAdmin: true,
          isLoggedIn: true,
          isMainUserIdentity: true,
          profile: {
            country: 'Mexico',
            country_id: 'MX',
            name: 'omegaUp admin',
            classname: 'user-rank-unranked',
            email: 'admin@omegaup.com',
            username: 'omegaup',
            verified: true,
          },
          userClassname: 'user-rank-unranked',
        },
        profileBadges: new Set(badge_alias),
        visitorBadges: new Set(badge_alias),
      },
    });
    expect(wrapper.find('[data-user-profile-root]').exists()).toBe(true);
  });
});
