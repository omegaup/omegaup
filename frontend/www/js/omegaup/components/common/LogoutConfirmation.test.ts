import { shallowMount, createLocalVue } from '@vue/test-utils';
import Vue, { ComponentOptions } from 'vue';
import BootstrapVue from 'bootstrap-vue';

import common_LogoutConfirmation from './LogoutConfirmation.vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);

describe('LogoutConfirmation.vue', () => {
  it('Should handle visibility and confirmation', async () => {
    const wrapper = shallowMount(
      common_LogoutConfirmation as ComponentOptions<Vue>,
      {
        localVue,
        propsData: {
          value: true,
        },
      },
    );

    expect((wrapper.vm as any).showModal).toBe(true);

    (wrapper.vm as any).showModal = false;
    expect(wrapper.emitted('input')).toEqual([[false]]);
  });
});
