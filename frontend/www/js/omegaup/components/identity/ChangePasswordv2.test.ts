import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import identity_ChangePasswordv2 from './ChangePasswordv2.vue';

describe('ChangePasswordv2.vue', () => {
  it('Should handle change password view for an identity given', () => {
    const wrapper = shallowMount(identity_ChangePasswordv2, {
      propsData: {
        username: 'hello omegaUp',
      },
    });

    expect(wrapper.text()).toContain('hello omegaUp');
  });
});
