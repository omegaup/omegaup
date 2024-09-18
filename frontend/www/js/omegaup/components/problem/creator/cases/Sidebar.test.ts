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
  // Total 6 buttons are rendered initially on this page.
  // - Layout button
  // - Add case/group button
  // - Ungrouped case button
  // - Add new layout button
  // - Add layout from selected case button
  // - close layout bar button
  const initialButtonsCount = 6;

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

    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(4);
    expect(
      wrapper.find('b-button-stub[title="ungroupedCase1"]').text(),
    ).toContain('ungroupedCase1');

    store.commit('casesStore/deleteGroup', newGroup1.groupID);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(3);

    store.commit('casesStore/addGroup', newGroup1);
    store.commit('casesStore/addGroup', newGroup2);
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(5);

    store.commit('casesStore/deleteUngroupedCases');
    await Vue.nextTick();
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(3);
  });

  it('should show groups and cases inside them', async () => {
    const wrapper = shallowMount(Sidebar, { localVue, store });

    const newGroup1 = generateGroup({
      name: 'group1',
      ungroupedCase: false,
    });
    const newGroup2 = generateGroup({
      name: 'group2withlongname',
      ungroupedCase: false,
    });
    store.commit('casesStore/addGroup', newGroup1);
    store.commit('casesStore/addGroup', newGroup2);
    expect(store.state.casesStore.groups).toStrictEqual([newGroup1, newGroup2]);

    await Vue.nextTick();
    await Vue.nextTick();

    const totalDropdownItemsCount = 13;
    // There are
    // - 5 dropdown items for each group
    // - 2 dropdown items for ungrouped case
    // - a dropdown item for validate points

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
    // (#dropdown-stubs) = 5*(#groups) + 2 (for ungrouped cases) + 1 (for validate points)

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
    expect(wrapper.findAll('b-dropdown-item-stub').length).toBe(8);
  });

  it('Should modify a group', async () => {
    const wrapper = mount(Sidebar, { localVue, store: store });

    const newGroup = generateGroup({
      name: 'group',
      autoPoints: true,
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

    const editAutoPointsInput = editModal.find(
      '[data-sidebar-edit-group-modal="edit autoPoints"]',
    );
    await editAutoPointsInput.setChecked(false);

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

  it('Should validate and fix points', async () => {
    const wrapper = mount(Sidebar, { localVue, store });

    const fixedPointsGroup1 = generateGroup({
      name: 'fixedPointsGroup',
      autoPoints: false,
      points: 100,
    });
    store.commit('casesStore/addGroup', fixedPointsGroup1);

    const newCase1 = generateCase({
      name: 'case1',
      autoPoints: false,
      points: 20,
      groupID: fixedPointsGroup1.groupID,
    });

    const newCase2 = generateCase({
      name: 'case2',
      autoPoints: false,
      points: 30,
      groupID: fixedPointsGroup1.groupID,
    });

    store.commit('casesStore/addCase', newCase1);
    store.commit('casesStore/addCase', newCase2);

    const fixedPointsGroup2 = generateGroup({
      name: 'fixedPointsGroup',
      autoPoints: false,
      points: 100,
    });
    store.commit('casesStore/addGroup', fixedPointsGroup2);

    const newCase3 = generateCase({
      name: 'case1',
      autoPoints: false,
      points: 200,
      groupID: fixedPointsGroup2.groupID,
    });

    const newCase4 = generateCase({
      name: 'case2',
      autoPoints: false,
      points: 300,
      groupID: fixedPointsGroup2.groupID,
    });

    store.commit('casesStore/addCase', newCase3);
    store.commit('casesStore/addCase', newCase4);

    const autoPointsGroup1 = generateGroup({
      name: 'autoPointsGroup',
      autoPoints: true,
    });
    store.commit('casesStore/addGroup', autoPointsGroup1);

    const newCase5 = generateCase({
      name: 'case1',
      autoPoints: false,
      points: 200,
      groupID: autoPointsGroup1.groupID,
    });

    const newCase6 = generateCase({
      name: 'case2',
      autoPoints: false,
      points: 300,
      groupID: autoPointsGroup1.groupID,
    });

    const newAutoPointsCase1 = generateCase({
      name: 'case3',
      autoPoints: true,
      groupID: autoPointsGroup1.groupID,
    });

    store.commit('casesStore/addCase', newCase5);
    store.commit('casesStore/addCase', newCase6);
    store.commit('casesStore/addCase', newAutoPointsCase1);

    const autoPointsGroup2 = generateGroup({
      name: 'autoPointsGroup',
      autoPoints: true,
    });
    store.commit('casesStore/addGroup', autoPointsGroup2);

    const newCase7 = generateCase({
      name: 'case1',
      autoPoints: false,
      points: 20,
      groupID: autoPointsGroup2.groupID,
    });

    const newCase8 = generateCase({
      name: 'case2',
      autoPoints: false,
      points: 30,
      groupID: autoPointsGroup2.groupID,
    });

    store.commit('casesStore/addCase', newCase7);
    store.commit('casesStore/addCase', newCase8);

    const autoPointsGroup3 = generateGroup({
      name: 'autoPointsGroup',
      autoPoints: true,
    });
    store.commit('casesStore/addGroup', autoPointsGroup3);

    const newCase9 = generateCase({
      name: 'case1',
      autoPoints: false,
      points: 20,
      groupID: autoPointsGroup3.groupID,
    });

    const newCase10 = generateCase({
      name: 'case2',
      autoPoints: false,
      points: 30,
      groupID: autoPointsGroup3.groupID,
    });

    const newAutoPointsCase2 = generateCase({
      name: 'case3',
      autoPoints: true,
      groupID: autoPointsGroup3.groupID,
    });

    store.commit('casesStore/addCase', newAutoPointsCase2);
    store.commit('casesStore/addCase', newCase9);
    store.commit('casesStore/addCase', newCase10);

    await Vue.nextTick();

    const validatePointsDropdownItem = wrapper.find(
      '[data-sidebar-validate-points-dropdown-item]',
    );
    await validatePointsDropdownItem.trigger('click');

    const validatePointsModal = wrapper.find(
      '[data-sidebar-validate-points-modal]',
    );
    await validatePointsModal.find('button.btn-success').trigger('click');

    expect(
      wrapper.vm.groups.find(
        (_group) => _group.groupID === fixedPointsGroup1.groupID,
      )?.points,
    ).toBe(100);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === fixedPointsGroup1.groupID)
        ?.cases.find((_case) => _case.caseID === newCase1.caseID)?.points,
    ).toBe(40);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === fixedPointsGroup1.groupID)
        ?.cases.find((_case) => _case.caseID === newCase2.caseID)?.points,
    ).toBe(60);

    expect(
      wrapper.vm.groups.find(
        (_group) => _group.groupID === fixedPointsGroup2.groupID,
      )?.points,
    ).toBe(100);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === fixedPointsGroup2.groupID)
        ?.cases.find((_case) => _case.caseID === newCase3.caseID)?.points,
    ).toBe(40);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === fixedPointsGroup2.groupID)
        ?.cases.find((_case) => _case.caseID === newCase4.caseID)?.points,
    ).toBe(60);

    expect(
      wrapper.vm.groups.find(
        (_group) => _group.groupID === autoPointsGroup1.groupID,
      )?.points,
    ).toBe(500);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup1.groupID)
        ?.cases.find((_case) => _case.caseID === newCase5.caseID)?.points,
    ).toBe(200);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup1.groupID)
        ?.cases.find((_case) => _case.caseID === newCase6.caseID)?.points,
    ).toBe(300);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup1.groupID)
        ?.cases.find((_case) => _case.caseID === newAutoPointsCase1.caseID)
        ?.points,
    ).toBe(0);

    expect(
      wrapper.vm.groups.find(
        (_group) => _group.groupID === autoPointsGroup2.groupID,
      )?.points,
    ).toBe(50);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup2.groupID)
        ?.cases.find((_case) => _case.caseID === newCase7.caseID)?.points,
    ).toBe(20);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup2.groupID)
        ?.cases.find((_case) => _case.caseID === newCase8.caseID)?.points,
    ).toBe(30);

    expect(
      wrapper.vm.groups.find(
        (_group) => _group.groupID === autoPointsGroup3.groupID,
      )?.points,
    ).toBe(100);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup3.groupID)
        ?.cases.find((_case) => _case.caseID === newCase9.caseID)?.points,
    ).toBe(20);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup3.groupID)
        ?.cases.find((_case) => _case.caseID === newCase10.caseID)?.points,
    ).toBe(30);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === autoPointsGroup3.groupID)
        ?.cases.find((_case) => _case.caseID === newAutoPointsCase2.caseID)
        ?.points,
    ).toBe(50);
  });
});
