import { createLocalVue, shallowMount } from '@vue/test-utils';

import Sidebar from './Sidebar.vue';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';

import T from '../../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Tabs.vue', () => {
  it('Should contain 3 buttons and Groups text', async () => {
    const wrapper = shallowMount(Sidebar, { localVue });

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(3);
    let shouldContainAddText = false;
    buttons.wrappers.forEach((button) => {
      if (button.text() === T.problemCreatorAdd) shouldContainAddText = true;
    });
    expect(shouldContainAddText).toBe(true);
    expect(wrapper.find('h5').text()).toBe(T.problemCreatorGroups);
  });
});
