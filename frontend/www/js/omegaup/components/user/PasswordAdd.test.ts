import { mount } from '@vue/test-utils';
import user_Password_Add from './PasswordAdd.vue';
import T from '../../lang';

describe('PasswordAdd.vue', () => {
  it('Should emit password add', async () => {
    const username = 'username';
    const wrapper = mount(user_Password_Add, {
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

  it('Should not emit password add when there is new password mismatch', async () => {
    const wrapper = mount(user_Password_Add, {
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

  it('Should enable submit button when there is no new password mismatch nor empty passwords', async () => {
    const wrapper = mount(user_Password_Add, {
      propsData: { username: 'username' },
    });

    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();
  });

  it('Should disable submit button when there is new password mismatch or empty inputs', async () => {
    let username = 'username';
    const wrapper = mount(user_Password_Add, {
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
