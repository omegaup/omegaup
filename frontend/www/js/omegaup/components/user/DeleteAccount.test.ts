import { createLocalVue, shallowMount } from '@vue/test-utils';
import { ModalPlugin } from 'bootstrap-vue';
import type { ComponentOptions } from 'vue';
import type Vue from 'vue';
import user_DeleteAccount from './DeleteAccount.vue';
import T from '../../lang';

const localVue = createLocalVue();
localVue.use(ModalPlugin);

describe('DeleteAccount.vue', () => {
  it('Should render delete account danger zone and button', () => {
    const wrapper = shallowMount(user_DeleteAccount as ComponentOptions<Vue>, {
      localVue,
    });

    expect(wrapper.find('.alert-heading').text()).toBe(T.wordsDangerZone);
    expect(wrapper.find('button.btn-danger').text()).toContain(T.wordsDelete);
  });

  it('Should open confirmation modal on delete button click', async () => {
    const wrapper = shallowMount(user_DeleteAccount as ComponentOptions<Vue>, {
      localVue,
    });

    expect(wrapper.vm.$data.showConfirmationModal).toBe(false);
    await wrapper.find('button.btn-danger').trigger('click');
    expect(wrapper.vm.$data.showConfirmationModal).toBe(true);
  });

  it('Should emit remove event on form submit', async () => {
    const wrapper = shallowMount(user_DeleteAccount as ComponentOptions<Vue>, {
      localVue,
    });

    await wrapper.find('form').trigger('submit');
    expect(wrapper.emitted('remove')).toBeDefined();
    expect(wrapper.emitted('remove')?.length).toBe(1);
  });

  it('Should emit request-delete-account when modal ok is triggered', async () => {
    const wrapper = shallowMount(user_DeleteAccount as ComponentOptions<Vue>, {
      localVue,
    });

    const modal = wrapper.findComponent({ name: 'BModal' });
    expect(modal.exists()).toBe(true);
    await modal.vm.$emit('ok');

    expect(wrapper.emitted('request-delete-account')).toBeDefined();
    expect(wrapper.emitted('request-delete-account')?.length).toBe(1);
  });
});
