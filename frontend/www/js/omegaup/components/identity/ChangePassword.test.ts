import { shallowMount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';

import identity_ChangePassword from './ChangePassword.vue';

describe('ChangePassword.vue', () => {
  const Component = identity_ChangePassword as ComponentOptions<Vue>;

  it('Should handle change password view for an identity given', () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        username: 'hello omegaUp',
      },
    });

    expect(wrapper.text()).toContain('hello omegaUp');
  });

  it('Should emit emit-change-password on form submit', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        username: 'hello omegaUp',
      },
    });

    await wrapper.setData({
      newPassword: 'secretPassword123',
      newPasswordRepeat: 'secretPassword123',
    });

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('emit-change-password')).toBeTruthy();
    expect(wrapper.emitted('emit-change-password')?.[0]).toEqual([
      'secretPassword123',
      'secretPassword123',
    ]);
  });

  it('Should emit emit-cancel on cancel button click', async () => {
    const wrapper = shallowMount(Component, {
      propsData: {
        username: 'hello omegaUp',
      },
    });

    await wrapper.find('button[type="reset"]').trigger('click');
    expect(wrapper.emitted('emit-cancel')).toBeTruthy();
  });
});
