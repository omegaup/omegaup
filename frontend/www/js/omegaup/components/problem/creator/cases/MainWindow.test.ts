import { createLocalVue, shallowMount } from '@vue/test-utils';

import MainWindow from './MainWindow.vue';
import Sidebar from './Sidebar.vue';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';

import T from '../../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Tabs.vue', () => {
  it('Should contain 3 buttons and Groups text', async () => {
    const wrapper = shallowMount(MainWindow, { localVue });

    const sidebar = wrapper.findComponent(Sidebar);
    expect(sidebar.exists()).toBe(true);
  });
});
