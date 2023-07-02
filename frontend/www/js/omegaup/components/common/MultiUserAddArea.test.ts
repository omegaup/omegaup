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

    it('should display tags when users are added', async () => {
        const wrapper = mount(MultiUserAddArea, {
            propsData: {
                users: ['test_user_1'],
            },
        });

        expect(wrapper.find('textarea').exists()).toBe(false);
        expect(wrapper.find('.users-list__item').exists()).toBe(true);
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

        await wrapper.find('.tags-input-remove').trigger('click');

        expect(wrapper.emitted('remove-user')).toEqual([['test_user_1']]);
    });

    it('should emit update-users when the usersList changes', async () => {
        const wrapper = mount(MultiUserAddArea, {
            propsData: {
                users: ['test_user_1'],
            },
        });

        await wrapper.find('.edit-icon').trigger('click');
        // TODO: Validate if this is the correct way to add users, otherwise replace it.
        await wrapper.find('textarea').setValue('test_user_2');

        expect(wrapper.emitted('update-users')).toEqual([
            [['test_user_1', 'test_user_2']],
        ]);
    });
});