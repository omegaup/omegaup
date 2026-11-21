import { shallowMount } from '@vue/test-utils';
import Vue from 'vue';
import type { ComponentOptions } from 'vue';
import T from '../../lang';

import login_PasswordRecover from './PasswordRecover.vue';

const PasswordRecover = login_PasswordRecover as ComponentOptions<Vue>;

describe('PasswordRecover.vue', () => {
  it('Should render the recover form', () => {
    const wrapper = shallowMount(PasswordRecover);

    expect(wrapper.text()).toContain(T.passwordResetRequestTitle);
    expect(wrapper.find('input[name="email"]').exists()).toBe(true);
  });

  it('Should emit forgot-password with the typed email', async () => {
    const wrapper = shallowMount(PasswordRecover);

    await wrapper.find('input[name="email"]').setValue('user@omegaup.com');
    await wrapper.find('button').trigger('click');

    expect(wrapper.emitted('forgot-password')).toEqual([['user@omegaup.com']]);
  });
});
