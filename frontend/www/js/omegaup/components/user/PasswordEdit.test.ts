import { shallowMount } from '@vue/test-utils';
import user_Password_Edit from './PasswordEdit.vue';
import T from '../../lang';

describe('PasswordEdit.vue', () => {
  it('Should emit password update', async () => {
    const wrapper = shallowMount(user_Password_Edit);

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

  it('Should emit password add', async () => {
    const username = 'username';
    const wrapper = shallowMount(user_Password_Edit, {
      propsData: { username },
    });
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('add-password')).toBeDefined();
    expect(wrapper.emitted('add-password')).toEqual([
      [
        {
          username,
          password: newPassword,
        },
      ],
    ]);
  });

  it('Should not emit password update when there is new password mismatch', async () => {
    const wrapper = shallowMount(user_Password_Edit);

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

  it('Should not emit password add when there is new password mismatch', async () => {
    const wrapper = shallowMount(user_Password_Edit, {
      propsData: { username: 'username' },
    });

    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword2';

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('div.invalid-message').text()).toBe(T.passwordMismatch);

    await wrapper.find('button[type="submit"]').trigger('submit');
    expect(wrapper.emitted('add-password')).toBeUndefined();
  });

  it('Should enable submit button when there is no new password mismatch nor empty passwords on password update', async () => {
    const wrapper = shallowMount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();
  });

  it('Should enable submit button when there is no new password mismatch nor empty passwords on password add', async () => {
    const wrapper = shallowMount(user_Password_Edit, {
      propsData: { username: 'username' },
    });

    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();
  });

  it('Should disable submit button when there is new password mismatch or empty inputs on password update', async () => {
    const wrapper = shallowMount(user_Password_Edit);

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

  it('Should disable submit button when there is new password mismatch or empty inputs on password add', async () => {
    let username = 'username';
    const wrapper = shallowMount(user_Password_Edit, {
      propsData: { username },
    });

    const newPassword = 'newPassword';
    let newPassword2 = 'newPassword2';

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();

    username = '';
    newPassword2 = 'newPassword';

    await wrapper.find('input[data-username]').setValue(username);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();

    username = 'username';
    newPassword2 = '';

    await wrapper.find('input[data-username]').setValue(username);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();
  });
});
