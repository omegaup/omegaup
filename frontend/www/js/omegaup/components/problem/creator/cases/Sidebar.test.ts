import { createLocalVue, shallowMount } from '@vue/test-utils';

import Sidebar from './Sidebar.vue';
import BootstrapVue, { IconsPlugin, BButton } from 'bootstrap-vue';
import store from '@/js/omegaup/problem/creator/store';
import Vue from 'vue';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

import T from '../../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Tabs.vue', () => {
  beforeEach(() => {
    store.commit('casesStore/resetStore');
  });

  it('Should contain 4 buttons and Groups text', async () => {
    const wrapper = shallowMount(Sidebar, { localVue, store });

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(4);
    let shouldContainAddText = false;
    buttons.wrappers.forEach((button) => {
      if (button.text() === T.problemCreatorAdd) shouldContainAddText = true;
    });
    expect(shouldContainAddText).toBe(true);
    expect(wrapper.find('h5').text()).toBe(T.problemCreatorGroups);
  });

  it('should show ungrouped testcases', async () => {
    const wrapper = shallowMount(Sidebar, { localVue, store });

    expect(wrapper.text()).toContain(T.problemCreatorUngrouped);

    const newGroup1 = generateGroup({
      name: 'ungroupedCase1',
      ungroupedCase: true,
    });
    const newGroup2 = generateGroup({
      name: 'ungroupedCase2',
      ungroupedCase: true,
    });
    store.commit('casesStore/addGroup', newGroup1);
    expect(store.state.casesStore.groups[0]).toBe(newGroup1);

    await Vue.nextTick();

    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(3);
    expect(
      wrapper.find('b-button-stub[title="ungroupedCase1"]').text(),
    ).toContain('ungroupedCase1');

    store.commit('casesStore/deleteGroup', newGroup1.groupID);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(2);

    store.commit('casesStore/addGroup', newGroup1);
    store.commit('casesStore/addGroup', newGroup2);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(4);

    store.commit('casesStore/deleteUngroupedCases');
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(2);
  });

  it('should show groups and cases inside them', async () => {
    const wrapper = shallowMount(Sidebar, { localVue, store });

    const newGroup1 = generateGroup({
      name: 'group1',
    });
    const newGroup2 = generateGroup({
      name: 'group2withlongname',
    });
    store.commit('casesStore/addGroup', newGroup1);
    store.commit('casesStore/addGroup', newGroup2);
    expect(store.state.casesStore.groups).toStrictEqual([newGroup1, newGroup2]);

    await Vue.nextTick();

    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(6);

    const group1 = wrapper.find('b-button-stub[title="group1"]');
    const group2 = wrapper.find('b-button-stub[title="group2withlongname"]');

    expect(group1.text()).toContain('group1');
    expect(group2.text()).toContain('group2withlongname');

    const newCase1 = generateCase({
      name: 'case1',
      groupID: newGroup1.groupID,
    });
    const newCase2 = generateCase({
      name: 'group2withlongname',
      groupID: newGroup1.groupID,
    });
    const newCase3 = generateCase({
      name: 'group2withlongname',
      groupID: newGroup2.groupID,
    });
    store.commit('casesStore/addCase', newCase1);
    store.commit('casesStore/addCase', newCase2);
    store.commit('casesStore/addCase', newCase3);

    await Vue.nextTick();

    expect(
      group1.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(4);
    expect(
      group2.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(3);

    store.commit('casesStore/deleteGroupCases', newGroup2.groupID);
    await Vue.nextTick();
    expect(
      group2.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(2);

    store.commit('casesStore/deleteCase', {
      groupID: newGroup1.groupID,
      caseID: newCase1.caseID,
    });
    await Vue.nextTick();
    expect(
      group1.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(3);

    store.commit('casesStore/deleteGroup', newGroup1.groupID);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(4);
  });
});
