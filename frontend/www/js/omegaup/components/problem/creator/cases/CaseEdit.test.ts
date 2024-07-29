import { createLocalVue, shallowMount, mount } from '@vue/test-utils';

import CaseEdit from './CaseEdit.vue';
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

describe('CaseEdit.vue', () => {
  const newUngroupedCasegroup = generateGroup({
    name: 'New Ungrouped Case Group',
    ungroupedCase: true,
  });
  const newUngroupedCase = generateCase({
    name: 'New Ungrouped Case',
    groupID: newUngroupedCasegroup.groupID,
  });
  const newGroup = generateGroup({
    name: 'New Group',
    ungroupedCase: false,
  });
  const newCase = generateCase({
    name: 'New Case',
    groupID: newGroup.groupID,
  });
  beforeEach(async () => {
    store.commit('casesStore/resetStore');
    store.commit('casesStore/addGroup', newUngroupedCasegroup);
    store.commit('casesStore/addCase', newUngroupedCase);
    store.commit('casesStore/addGroup', newGroup);
    store.commit('casesStore/addCase', newCase);
  });

  it('Should show an ungrouped case', async () => {
    const wrapper = shallowMount(CaseEdit, { localVue, store: store });

    const groupID = newUngroupedCasegroup.groupID;
    const caseID = newUngroupedCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(6);

    expect(wrapper.text()).toContain(newUngroupedCase.name);
    expect(wrapper.text()).toContain(newUngroupedCasegroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('b-dropdown-stub').exists()).toBe(true);
  });

  it('Should show a grouped case', async () => {
    const wrapper = shallowMount(CaseEdit, { localVue, store: store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(6);

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    expect(
      wrapper.find('biconpencilfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorEditCase);
    expect(
      wrapper.find('bicontrashfill-stub').element.parentElement?.textContent,
    ).toContain(T.problemCreatorDeleteCase);
    expect(wrapper.find('b-dropdown-stub').exists()).toBe(true);
  });

  it('calls deleteLinesForSelectedCase when the delete option is clicked', async () => {
    const wrapper = shallowMount(CaseEdit, { localVue, store: store });
    expect(wrapper.find('biconthreedotsvertical-stub').exists()).toBe(true);
  });

  it('Should add, modify and delete a line', async () => {
    const wrapper = mount(CaseEdit, { localVue, store: store });

    const groupID = newGroup.groupID;
    const caseID = newCase.caseID;
    store.commit('casesStore/setSelected', {
      groupID,
      caseID,
    });
    await Vue.nextTick();

    expect(wrapper.findAll('b-button-stub[class="w-100"]').length).toBe(3);
    const dropdownButtons = wrapper.findAll('b-button-stub[class="w-100"]');

    expect(dropdownButtons.at(0).text()).toBe(T.problemCreatorLinesDelete);
    expect(dropdownButtons.at(1).text()).toBe(T.problemCreatorCaseDownloadIn);
    expect(dropdownButtons.at(2).text()).toBe(T.problemCreatorCaseDownloadTxt);
    const buttons = wrapper.findAllComponents(BButton);
    expect(buttons.length).toBe(4);

    expect(wrapper.text()).toContain(newCase.name);
    expect(wrapper.text()).toContain(newGroup.name);

    wrapper.vm.addNewLine();
    await Vue.nextTick();

    expect(wrapper.find('table').exists()).toBe(true);
    const formInputs = wrapper.findAll('input');

    formInputs.at(0).setValue('testLabel');
    formInputs.at(1).setValue('testValue');
    await wrapper.trigger('click');

    expect(wrapper.vm.getLinesFromSelectedCase[0].label).toBe('testLabel');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.value).toBe('testValue');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('line');

    const dropdowns = wrapper.findAll('a.dropdown-item');
    expect(dropdowns.length).toBe(4);

    await dropdowns.at(1).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('multiline');

    await dropdowns.at(2).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('array');

    await dropdowns.at(3).trigger('click');
    expect(wrapper.vm.getLinesFromSelectedCase[0].data.kind).toBe('matrix');

    wrapper.vm.deleteLine(wrapper.vm.getLinesFromSelectedCase[0].lineID);
    await Vue.nextTick();

    const formInputsUpdated = wrapper.findAll('input');
    expect(formInputsUpdated.length).toBe(0);
  });
});
