import { shallowMount } from '@vue/test-utils';

import CasesTab from './CasesTab.vue';
import Sidebar from './Sidebar.vue';
import AddPanel from './AddPanel.vue';
import Vue from 'vue';

describe('Tabs.vue', () => {
  it('Should contain 3 buttons and Groups text', async () => {
    const wrapper = shallowMount(CasesTab, {});

    const sidebar = wrapper.findComponent(Sidebar);
    expect(sidebar.exists()).toBe(true);
  });
  it('Should render "AddPanel.vue" conditionally', async () => {
    const wrapper = shallowMount(CasesTab, {});

    let addWindow = wrapper.findComponent(AddPanel);
    expect(addWindow.element).toBeUndefined();

    wrapper.setData({ shouldShowAddWindow: true });

    await Vue.nextTick();

    addWindow = wrapper.findComponent(AddPanel);
    expect(addWindow.element).not.toBeUndefined();
  });
});
