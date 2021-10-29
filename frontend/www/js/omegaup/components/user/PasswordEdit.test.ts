import { shallowMount } from '@vue/test-utils';
import user_Password_Edit from './PasswordEdit.vue';

describe('PasswordEdit.vue', () => {
  it('Should emit password update', () => {
    const wrapper = shallowMount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    wrapper.find('input[data-old-password]').setValue(oldPassword);
    wrapper.find('input[data-new-password]').setValue(newPassword);
    wrapper.find('input[data-new-password2]').setValue(newPassword2);

    wrapper.find('button[type="submit"]').trigger('submit');

    expect(wrapper.emitted('update-password')).toBeDefined();
    expect(wrapper.emitted('update-password')?.[0]).toEqual([
      {
        oldPassword: oldPassword,
        newPassword: newPassword,
      },
    ]);
  });

  it('Should not emit password update when there is new password mismatch', () => {
    const wrapper = shallowMount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword2';

    wrapper.find('input[data-old-password]').setValue(oldPassword);
    wrapper.find('input[data-new-password]').setValue(newPassword);
    wrapper.find('input[data-new-password2]').setValue(newPassword2);

    wrapper.find('button[type="submit"]').trigger('submit');

    expect(wrapper.emitted('update-password')).toBeUndefined();
  });
});
