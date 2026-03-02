import { mount } from '@vue/test-utils';
import user_Password_Edit from './PasswordEdit.vue';
import T from '../../lang';

describe('PasswordEdit.vue', () => {
  it('Should emit password update', async () => {
    const wrapper = mount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-password')).toBeDefined();
    expect(wrapper.emitted('update-password')).toEqual([
      [
        {
          oldPassword: oldPassword,
          newPassword: newPassword,
        },
      ],
    ]);
  });

  it('Should not emit password update when there is new password mismatch', async () => {
    const wrapper = mount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword2';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('div.invalid-message').text()).toBe(T.passwordMismatch);

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('update-password')).toBeUndefined();
  });

  it('Should enable submit button when there is no new password mismatch nor empty passwords', async () => {
    const wrapper = mount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();
  });

  it('Should disable submit button when there is new password mismatch or empty inputs', async () => {
    const wrapper = mount(user_Password_Edit);

    let oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    let newPassword2 = 'newPassword2';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();

    oldPassword = '';
    newPassword2 = 'newPassword';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();

    oldPassword = 'oldPassword';
    newPassword2 = '';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();
  });
});
