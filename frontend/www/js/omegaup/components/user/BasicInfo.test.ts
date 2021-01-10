import { shallowMount } from '@vue/test-utils';
import expect from 'expect';
import { types } from '../../api_types';

import user_BasicInfo from './BasicInfo.vue';

describe('BasicInfo.vue', () => {
  it('Should display user email', () => {
    const email = 'test@omegaup.com';
    const wrapper = shallowMount(user_BasicInfo, {
      propsData: {
        profile: <types.UserProfile>{ email: email },
        rank: 'Ω',
      },
    });
    expect(wrapper.find('[data-email]').text()).toBe(email);
  });
});
