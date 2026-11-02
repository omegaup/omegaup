import { mount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import T from '../../lang';

import login_PasswordReset from './PasswordReset.vue';

const PasswordReset = login_PasswordReset as ComponentOptions<Vue>;

describe('PasswordReset.vue', () => {
  const propsData = {
    email: 'user@omegaup.com',
    resetToken: 'reset-token',
  };

  it('Should render the reset form', () => {
    const wrapper = mount(PasswordReset, { propsData });

    expect(wrapper.text()).toContain(T.passwordResetResetTitle);
    expect(wrapper.find('input[name="password"]').exists()).toBe(true);
    expect(wrapper.find('input[name="password_confirmation"]').exists()).toBe(
      true,
    );
  });

  it('Should emit reset-password with the props and typed passwords', async () => {
    const wrapper = mount(PasswordReset, { propsData });

    await wrapper.find('input[name="password"]').setValue('newpassword');
    await wrapper
      .find('input[name="password_confirmation"]')
      .setValue('newpassword');
    await wrapper.find('button.btn-primary').trigger('click');

    expect(wrapper.emitted('reset-password')).toEqual([
      ['user@omegaup.com', 'reset-token', 'newpassword', 'newpassword'],
    ]);
  });
});
