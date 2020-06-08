import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import user_BasicInfo from './BasicInfov2.vue';

describe('BasicInfov2.vue', () => {
  it('Should display user email', () => {
    const wrapper = shallowMount(user_BasicInfo, {
      propsData: {
        profile: { email: 'test@omegaup.com' },
        rank: null,
      },
    });
    expect(wrapper.find('.render-if-email').exists()).toBe(true);
  });
});
