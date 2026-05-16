import { shallowMount } from '@vue/test-utils';
import type { types } from '../../api_types';

import user_BasicInfo from './BasicInfov2.vue';

describe('BasicInfov2.vue', () => {
  it('Should display user email', () => {
    const email = 'test@omegaup.com';
    const wrapper = shallowMount(user_BasicInfo, {
      props: {
        profile: {
          email: email,
          is_own_profile: true,
        } as types.UserProfile,
        rank: 'Ω',
      },
    });
    expect(wrapper.find('[data-email]').text()).toBe(email);
  });
});
