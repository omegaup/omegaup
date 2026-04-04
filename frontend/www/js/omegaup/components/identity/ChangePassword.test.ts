import { shallowMount } from '@vue/test-utils';

import identity_ChangePassword from './ChangePassword.vue';

describe('ChangePassword.vue', () => {
  it('Should handle change password view for an identity given', () => {
    const wrapper = shallowMount(identity_ChangePassword, {
      propsData: {
        username: 'hello omegaUp',
      },
    });

    expect(wrapper.text()).toContain('hello omegaUp');
  });
});
