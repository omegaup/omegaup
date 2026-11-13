import { mount } from '@vue/test-utils';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_BasicEdit from './BasicEdit.vue';
import * as ui from '../../ui';
import T from '../../lang';

describe('BasicEdit.vue', () => {
  it('Should show error when passwords do not match', async () => {
    const errorSpy = jest.spyOn(ui, 'error').mockImplementation(() => {});
    const wrapper = mount(user_BasicEdit as ComponentOptions<Vue>, {
      propsData: {
        username: 'john_doe',
      },
    });

    await wrapper
      .find('input[name="new-password-1"]')
      .setValue('secret_password_1');
    await wrapper
      .find('input[name="new-password-2"]')
      .setValue('secret_password_2');

    await wrapper.find('form').trigger('submit');

    expect(errorSpy).toHaveBeenCalledWith(T.userPasswordMustBeSame);
    expect(wrapper.emitted('update')).toBeUndefined();
    errorSpy.mockRestore();
  });

  it('Should emit update when passwords match', async () => {
    const wrapper = mount(user_BasicEdit as ComponentOptions<Vue>, {
      propsData: {
        username: 'john_doe',
      },
    });

    await wrapper
      .find('input[name="new-password-1"]')
      .setValue('matching_password');
    await wrapper
      .find('input[name="new-password-2"]')
      .setValue('matching_password');

    await wrapper.find('form').trigger('submit');

    expect(wrapper.emitted('update')).toBeDefined();
    expect(wrapper.emitted('update')?.[0]).toEqual([
      'john_doe',
      'matching_password',
    ]);
  });

  it('Should allow editing username locally without mutating prop and sync prop updates', async () => {
    const errorSpy = jest.spyOn(console, 'error').mockImplementation(() => {});
    const warnSpy = jest.spyOn(console, 'warn').mockImplementation(() => {});
    const wrapper = mount(user_BasicEdit as ComponentOptions<Vue>, {
      propsData: {
        username: 'john_doe',
      },
    });

    await wrapper.find('input[name="username"]').setValue('jane_doe');
    await wrapper
      .find('input[name="new-password-1"]')
      .setValue('matching_password');
    await wrapper
      .find('input[name="new-password-2"]')
      .setValue('matching_password');
    await wrapper.find('form').trigger('submit');

    expect(wrapper.props('username')).toBe('john_doe');
    expect(wrapper.emitted('update')?.[0]).toEqual([
      'jane_doe',
      'matching_password',
    ]);

    await wrapper.setProps({ username: 'updated_user' });
    await wrapper.find('form').trigger('submit');

    expect(wrapper.emitted('update')?.[1]).toEqual([
      'updated_user',
      'matching_password',
    ]);
    expect(errorSpy).not.toHaveBeenCalled();
    expect(warnSpy).not.toHaveBeenCalled();
    errorSpy.mockRestore();
    warnSpy.mockRestore();
  });
});
