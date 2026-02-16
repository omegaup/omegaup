import { createLocalVue, mount } from '@vue/test-utils';

import LayoutSidebar from './LayoutSidebar.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';
import store from '@/js/omegaup/problem/creator/store';
import {
  generateCase,
  generateGroup,
} from '@/js/omegaup/problem/creator/modules/cases';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('LayoutSidebar.vue', () => {
  store.commit('casesStore/addNewLayout');
  const newUngroupedCasegroup = generateGroup({
    name: 'new_ungrouped_case',
    ungroupedCase: true,
  });
  const newUngroupedCase = generateCase({
    name: 'new_ungrouped_case',
    groupID: newUngroupedCasegroup.groupID,
  });
  const newGroup = generateGroup({
    name: 'new_group',
    ungroupedCase: false,
  });
  const newCase = generateCase({
    name: 'new_case',
    groupID: newGroup.groupID,
  });
  store.commit('casesStore/addGroup', newUngroupedCasegroup);
  store.commit('casesStore/addCase', newUngroupedCase);
  store.commit('casesStore/addGroup', newGroup);
  store.commit('casesStore/addCase', newCase);
  const ungroupedCaseGroupID = newUngroupedCasegroup.groupID;
  const ungroupedCaseCaseID = newUngroupedCase.caseID;
  const groupedCaseGroupID = newGroup.groupID;
  const groupedCaseCaseID = newCase.caseID;

  // Currently, there are 4 methods on layout:
  // - Rename layout.
  // - Enforce layout to the selected case.
  // - Enforce layout to all the cases.
  // - Copy layout.
  // - Delete Layout.
  const layoutDropdownButtonCounts = 5;

  store.commit('casesStore/setSelected', {
    groupID: ungroupedCaseGroupID,
    caseID: ungroupedCaseCaseID,
  });
  it('Should show layouts and methods', async () => {
    const wrapper = mount(LayoutSidebar, {
      localVue,
      store,
    });

    expect(wrapper.vm.getAllLayouts.length).toBe(1);
    expect(
      wrapper.vm.getAllLayouts.filter(
        (_layout) => _layout.name === T.problemCreatorLayoutNew,
      ).length,
    ).toBe(1);

    const layoutDropdown = wrapper.find('div[data-layout-dropdown]');
    expect(layoutDropdown.text()).toContain(T.problemCreatorLayoutNew);

    const dropdownButtons = layoutDropdown.findAll('a.dropdown-item');
    expect(dropdownButtons.length).toBe(layoutDropdownButtonCounts);

    const addLineInfoButton = wrapper.find('button[data-layout-add-line-info]');
    await addLineInfoButton.trigger('click');

    // Rows of `line info` form a table in their primitive form.
    expect(wrapper.find('table.table').exists()).toBeTruthy();

    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === ungroupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === ungroupedCaseCaseID)?.lines
        .length,
    ).toBe(0);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === groupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === groupedCaseCaseID)?.lines
        .length,
    ).toBe(0);

    const enforceLayoutToSelectedButton = layoutDropdown.find(
      'a[data-layout-dropdown-enforce-to-selected]',
    );
    await enforceLayoutToSelectedButton.trigger('click');

    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === ungroupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === ungroupedCaseCaseID)?.lines
        .length,
    ).toBe(1);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === groupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === groupedCaseCaseID)?.lines
        .length,
    ).toBe(0);

    const enforceLayoutToAllButton = layoutDropdown.find(
      'a[data-layout-dropdown-enforce-to-all]',
    );
    await enforceLayoutToAllButton.trigger('click');

    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === ungroupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === ungroupedCaseCaseID)?.lines
        .length,
    ).toBe(1);
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === groupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === groupedCaseCaseID)?.lines
        .length,
    ).toBe(1);

    const copyLayoutButton = layoutDropdown.find(
      'a[data-layout-dropdown-copy]',
    );
    await copyLayoutButton.trigger('click');

    expect(wrapper.vm.getAllLayouts.length).toBe(2);
    expect(wrapper.vm.getAllLayouts[1].name).toBe(
      T.problemCreatorLayoutNew + T.problemCreatorLayoutWordCopy,
    );

    const deleteLayoutButton = layoutDropdown.find(
      'a[data-layout-dropdown-delete]',
    );
    await deleteLayoutButton.trigger('click');

    expect(wrapper.vm.getAllLayouts.length).toBe(1);
    expect(
      wrapper.vm.getAllLayouts.filter(
        (_layout) => _layout.name === T.problemCreatorLayoutNew,
      ).length,
    ).toBe(0);

    store.dispatch('casesStore/addNewLine');
    expect(
      wrapper.vm.groups
        .find((_group) => _group.groupID === ungroupedCaseGroupID)
        ?.cases.find((_case) => _case.caseID === ungroupedCaseCaseID)?.lines
        .length,
    ).toBe(2);

    store.commit('casesStore/addLayoutFromSelectedCase');
    await Vue.nextTick();
    const layoutDropdownNew = wrapper
      .findAll('div[data-layout-dropdown]')
      .at(1);
    expect(layoutDropdownNew.text()).toContain(
      newUngroupedCasegroup.name + '_' + newUngroupedCase.name,
    );
  });

  it('Should rename layouts', async () => {
    store.commit('casesStore/resetStore');
    store.commit('casesStore/addNewLayout');
    const wrapper = mount(LayoutSidebar, {
      localVue,
      store,
    });

    expect(wrapper.vm.getAllLayouts.length).toBe(1);
    expect(
      wrapper.vm.getAllLayouts.filter(
        (_layout) => _layout.name === T.problemCreatorLayoutNew,
      ).length,
    ).toBe(1);

    const layoutDropdown = wrapper.find('div[data-layout-dropdown]');
    expect(layoutDropdown.text()).toContain(T.problemCreatorLayoutNew);

    const renameLayout = wrapper.find('[data-layout-dropdown-rename-layout]');
    await renameLayout.trigger('click');

    const renameLayoutModal = wrapper.find(
      '[data-layout-dropdown-rename-modal]',
    );
    expect(renameLayoutModal.exists).toBeTruthy();

    const renameLayoutForm = wrapper.find(
      '[data-layout-sidebar-rename-layout]',
    );
    const layoutName = 'Hello layout';
    renameLayoutForm.setValue(layoutName);
    expect((renameLayoutForm.element as HTMLInputElement).value).toBe(
      layoutName,
    );

    const targetLayout = wrapper.vm.getAllLayouts[0];
    wrapper.vm.editLayoutName([
      targetLayout.layoutID,
      (renameLayoutForm.element as HTMLInputElement).value,
    ]);

    await Vue.nextTick();
    const layoutDropdownUpdated = wrapper.find('div[data-layout-dropdown]');
    expect(layoutDropdownUpdated.text()).toContain(layoutName);
  });
});
