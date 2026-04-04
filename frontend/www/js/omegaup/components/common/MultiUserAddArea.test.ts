import { mount } from '@vue/test-utils';
import MultiUserAddArea from './MultiUserAddArea.vue';

describe('MultiUserAddArea.vue', () => {
  it('should display textarea when no users are added', async () => {
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: [],
      },
    });

    expect(wrapper.find('textarea').exists()).toBe(true);
  });

  it('should display a list of users if an array of users is passed', async () => {
    const usersList = ['test_user_1', 'test_user_2'];
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: usersList,
      },
    });

    expect(wrapper.find('textarea').exists()).toBe(false);
    expect(wrapper.findAll('.users-list__item').length).toBe(usersList.length);
  });

  it('should enable the textarea when user clicks on the edit button', async () => {
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: ['test_user_1'],
      },
    });

    expect(wrapper.find('textarea').exists()).toBe(false);
    expect(wrapper.find('.users-list__item').exists()).toBe(true);

    await wrapper.find('.edit-icon').trigger('click');

    expect(wrapper.find('textarea').exists()).toBe(true);
    expect(wrapper.find('.users-list__item').exists()).toBe(false);
  });

  it('should call removeUser when user clicks on the remove button', async () => {
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: ['test_user_1'],
      },
    });

    wrapper.vm.removeUser = jest.fn();

    // search for the list item containing the user
    const listItem = wrapper.find('.users-list__item');
    expect(listItem.exists()).toBe(true);

    // click on the remove button
    await listItem.find('.tags-input-remove').trigger('click');
    await wrapper.vm.$nextTick();

    expect(wrapper.vm.removeUser).toHaveBeenCalled();
  });

  it('should parse users when user paste them in the textarea', async () => {
    // Given: A textarea without users
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: [],
      },
    });

    // When: The user pastes a list of users
    await wrapper
      .find('textarea')
      .setValue('test_user_1,test_user_2,test_user_3');
    // await for a second to let the textarea parse the users
    await new Promise((resolve) => setTimeout(resolve, 1000));
    await wrapper.vm.$nextTick();

    // Then: The users are parsed and added to the list
    expect(wrapper.findAll('.users-list__item').length).toBe(3);
  });

  it('should emit update-users when the usersList changes', async () => {
    const wrapper = mount(MultiUserAddArea, {
      propsData: {
        users: ['test_user_1'],
      },
    });

    await wrapper.find('.edit-icon').trigger('click');
    await wrapper.vm.$nextTick();

    // TODO: Validate if this is the correct way to add users, otherwise replace it.
    await wrapper.find('textarea').setValue('test_user_2');
    await new Promise((resolve) => setTimeout(resolve, 1000));
    await wrapper.vm.$nextTick();

    expect(wrapper.emitted('update-users')).toBeTruthy();
  });
});
