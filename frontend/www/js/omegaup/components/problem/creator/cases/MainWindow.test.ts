import { createLocalVue, shallowMount } from '@vue/test-utils';

import MainWindow from './MainWindow.vue';
import Sidebar from './Sidebar.vue';
import AddWindow from './AddWindow.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import Vue from 'vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Tabs.vue', () => {
  it('Should contain 3 buttons and Groups text', async () => {
    const wrapper = shallowMount(MainWindow, { localVue });

    const sidebar = wrapper.findComponent(Sidebar);
    expect(sidebar.exists()).toBe(true);
  });
  it('Should render "AddWindow.vue" conditionally', async () => {
    const wrapper = shallowMount(MainWindow, { localVue });

    let addWindow = wrapper.findComponent(AddWindow);
    expect(addWindow.element).not.toBeVisible();

    wrapper.setData({ shouldShowAddWindow: true });

    await Vue.nextTick();

    expect(addWindow.element).toBeVisible();
  });
});
