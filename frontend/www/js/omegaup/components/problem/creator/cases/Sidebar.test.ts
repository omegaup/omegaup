import { createLocalVue, shallowMount, mount } from '@vue/test-utils';

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

describe('Sidebar.vue', () => {
  // Total 7 buttons are rendered initially on this page.
  // - Layout button
  // - Add case/group button
  // - Delete group button
  // - Delete Cases button
  // - Add new layout button
  // - Add layout from selected case button
  // - close layout bar button
  const initialButtonsCount = 7;

  beforeEach(() => {
    store.commit('casesStore/resetStore');
  });

  it('Should contain buttons and Groups text', async () => {
    const wrapper = shallowMount(Sidebar, { localVue, store });

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(initialButtonsCount);
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

    const totalDropdownItemsCount = 12;
    // There are
    // - 5 dropdown items for each group
    // - a dropdown item for each case
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(
      totalDropdownItemsCount,
    );

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

    // The number of dropdown stubs should be equal to
    // (#dropdown-stubs) = 5*(#groups) + 1*(#cases)

    expect(
      group1.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(7);
    expect(
      group2.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(6);

    store.commit('casesStore/deleteGroupCases', newGroup2.groupID);
    await Vue.nextTick();
    expect(
      group2.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(5);

    store.commit('casesStore/deleteCase', {
      groupID: newGroup1.groupID,
      caseID: newCase1.caseID,
    });
    await Vue.nextTick();
    expect(
      group1.element.parentElement?.querySelectorAll('b-dropdown-item-stub')
        .length,
    ).toBe(6);

    store.commit('casesStore/deleteGroup', newGroup1.groupID);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(7);
  });

  it('Should modify a group', async () => {
    const wrapper = mount(Sidebar, { localVue, store: store });

    const newGroup = generateGroup({
      name: 'group',
    });
    store.commit('casesStore/addGroup', newGroup);
    await Vue.nextTick();

    const editButton = wrapper.find(
      '[data-sidebar-edit-group-dropdown="edit group"]',
    );
    await editButton.trigger('click');

    const editModal = wrapper.find('[data-sidebar-edit-group-modal=""]');

    const modifiedName = 'modifiedgroup';
    const editNameInput = editModal.find(
      '[data-sidebar-edit-group-modal="edit name"]',
    );
    await editNameInput.setValue(modifiedName);

    const modifiedPoints = 100;
    const editPointsInput = editModal.find(
      '[data-sidebar-edit-group-modal="edit points"]',
    );
    await editPointsInput.setValue(modifiedPoints);

    await editModal.find('button.btn-success').trigger('click');

    expect(
      wrapper.vm.groups.find((_group) => _group.groupID === newGroup.groupID)
        ?.name,
    ).toBe(modifiedName);
    expect(
      wrapper.vm.groups.find((_group) => _group.groupID === newGroup.groupID)
        ?.points,
    ).toBe(modifiedPoints);
  });

  it('Should download a group', async () => {
    const wrapper = mount(Sidebar, { localVue, store: store });

    const newGroup = generateGroup({
      name: 'group',
    });
    store.commit('casesStore/addGroup', newGroup);
    await Vue.nextTick();

    const downloadInButton = wrapper.find(
      '[data-sidebar-edit-group-dropdown="download .in"]',
    );
    const downloadTxtButton = wrapper.find(
      '[data-sidebar-edit-group-dropdown="download .txt"]',
    );

    const mockDownload = jest.spyOn(wrapper.vm, 'downloadGroupInput');

    await downloadInButton.trigger('click');
    expect(mockDownload).toHaveBeenCalledWith(newGroup.groupID, '.in');

    await downloadTxtButton.trigger('click');
    expect(mockDownload).toHaveBeenCalledWith(newGroup.groupID, '.txt');

    expect(wrapper.emitted()['download-zip-file']?.length).toBe(2);
  });
});
