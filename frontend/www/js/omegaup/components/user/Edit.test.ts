import { shallowMount } from '@vue/test-utils';
import { types } from '../../api_types';
import user_Edit from './Edit.vue';

describe('Edit.vue', () => {
  it('Should show cancel button href', () => {
    const wrapper = shallowMount(user_Edit, {
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
          is_own_profile: true,
        } as types.UserProfileInfo,
        inProduction: true,
      },
    });
    expect(wrapper.find('a[href="/profile/"]').exists()).toBe(true);
  });
});
