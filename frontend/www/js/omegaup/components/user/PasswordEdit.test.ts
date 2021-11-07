import { shallowMount } from '@vue/test-utils';
import user_Password_Edit from './PasswordEdit.vue';
import T from '../../lang';

describe('PasswordEdit.vue', () => {
  it('Should emit password update', async () => {
    let wrapper = shallowMount(user_Password_Edit);

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

    const username = 'username';
    wrapper = shallowMount(user_Password_Edit, {
      propsData: { username },
    });

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

  it('Should enable submit button when there is no new password mismatch nor empty passwords', async () => {
    let wrapper = shallowMount(user_Password_Edit);

    const oldPassword = 'oldPassword';
    const newPassword = 'newPassword';
    const newPassword2 = 'newPassword';

    await wrapper.find('input[data-old-password]').setValue(oldPassword);
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();

    wrapper = shallowMount(user_Password_Edit, {
      propsData: { username: 'username' },
    });

    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeEnabled();
  });

  it('Should disable submit button when there is new password mismatch or empty passwords', async () => {
    let wrapper = shallowMount(user_Password_Edit);

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

    wrapper = shallowMount(user_Password_Edit, {
      propsData: { username: 'username' },
    });

    await wrapper.find('input[data-username]').setValue('');
    await wrapper.find('input[data-new-password]').setValue(newPassword);
    await wrapper.find('input[data-new-password2]').setValue(newPassword2);
    expect(wrapper.find('button[type="submit"]').element).toBeDisabled();
  });
});
