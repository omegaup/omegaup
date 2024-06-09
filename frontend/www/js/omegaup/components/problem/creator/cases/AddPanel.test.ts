import { createLocalVue, mount, Wrapper } from '@vue/test-utils';

import AddPanel from './AddPanel.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';
import vuexStore from '../../../../problem/creator/store';
import { StoreState } from '../../../../problem/creator/types';
import { Store } from 'vuex';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('AddPanel.vue', () => {
  beforeEach(() => {
    vuexStore.commit('casesStore/resetStore');
  });
  it('Should add a case to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    await Vue.nextTick();

    const nameInput = wrapper.find('input[name="case-name"]');

    await nameInput.setValue('caseName');

    const updatedNameInput = wrapper.find('input[name="case-name"]')
      .element as HTMLInputElement;

    expect(updatedNameInput.value).toBe('casename'); // Tests the formatter

    // We need to use any since vue components methods are not stored in vm's type
    await (wrapper.vm as any).addItemToStore();

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(1);
    expect(groups[0].name).toBe('casename');
    expect(groups[0].autoPoints).toBe(true);
    expect(groups[0].ungroupedCase).toBe(true);
  });
  it('Should add a group to the store and add a case to the group', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    wrapper.setData({ tab: 'group' });

    await Vue.nextTick();

    const nameInput = wrapper.find('input[name="group-name"]');
    const pointsInput = wrapper.find('input[name="group-points"]');

    await nameInput.setValue('testgroup');
    await pointsInput.setValue(10);

    const updatedNameInput = wrapper.find('input[name="group-name"]')
      .element as HTMLInputElement;
    const updatedPointsInput = wrapper.find('input[name="group-points"]')
      .element as HTMLInputElement;

    expect(updatedNameInput.value).toBe('testgroup');
    expect(updatedPointsInput.value).toBe('10');

    await (wrapper.vm as any).addItemToStore();

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(1);
    expect(groups[0].name).toBe('testgroup');
    expect(groups[0].autoPoints).toBe(false);
    expect(groups[0].points).toBe(10);
    expect(groups[0].ungroupedCase).toBe(false);

    const groupId = groups?.[0].groupID;

    wrapper.setData({ tab: 'case' });
    await Vue.nextTick();

    const caseNameInput = wrapper.find('input[name="case-name"]');
    const casePointsInput = wrapper.find('input[name="case-points"]');
    const caseGroupName = wrapper.find('select[name="case-group"]');

    await caseNameInput.setValue('groupedtestcase');
    await casePointsInput.setValue(10);
    await caseGroupName.setValue(groupId);

    await wrapper.find('form').trigger('submit.prevent');

    const groupedCases = store.state.casesStore.groups?.[0].cases;
    expect(groupedCases.length).toBe(1);
    expect(groupedCases[0].name).toBe('groupedtestcase');
    expect(groupedCases[0].groupID).toBe(groupId);
    expect(groupedCases[0].points).toBe(10);
  });
  it('Should add an ungrouped case to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    wrapper.setData({ tab: 'case' });
    await Vue.nextTick();

    const nameInput = wrapper.find('input[name="case-name"]');
    const pointsInput = wrapper.find('input[name="case-points"]');

    await nameInput.setValue('testcase');
    await pointsInput.setValue(50);

    const updatedNameInput = wrapper.find('input[name="case-name"]')
      .element as HTMLInputElement;
    const updatedPointsInput = wrapper.find('input[name="case-points"]')
      .element as HTMLInputElement;

    expect(updatedNameInput.value).toBe('testcase');
    expect(updatedPointsInput.value).toBe('50');

    await wrapper.find('form').trigger('submit.prevent');

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(1);
    expect(groups[0].name).toBe('testcase');
    expect(groups[0].autoPoints).toBe(false);
    expect(groups[0].points).toBe(50);
    expect(groups[0].ungroupedCase).toBe(true);
  });
  it('Should add multiple ungrouped cases to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    wrapper.setData({ tab: 'multiplecases' });
    await Vue.nextTick();

    const prefixInput = wrapper.find('input[name="multiple-cases-prefix"]');
    const suffixInput = wrapper.find('input[name="multiple-cases-suffix"]');
    const countInput = wrapper.find('input[name="multiple-cases-count"]');

    await prefixInput.setValue('test');
    await suffixInput.setValue('case');
    await countInput.setValue(10);

    const updatedpPrefixInput = wrapper.find(
      'input[name="multiple-cases-prefix"]',
    ).element as HTMLInputElement;
    const updatedSuffixInput = wrapper.find(
      'input[name="multiple-cases-suffix"]',
    ).element as HTMLInputElement;
    const updatedCountInput = wrapper.find('input[name="multiple-cases-count"]')
      .element as HTMLInputElement;

    expect(updatedpPrefixInput.value).toBe('test');
    expect(updatedSuffixInput.value).toBe('case');
    expect(updatedCountInput.value).toBe('10');

    await wrapper.find('form').trigger('submit.prevent');

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(10);
    for (let i = 0; i < 10; i++) {
      expect(groups[i].name).toBe('test' + String(i + 1) + 'case');
      expect(groups[i].points).toBe(0);
      expect(groups[i].ungroupedCase).toBe(true);
    }
  });
  it('Should contain 3 tabs', async () => {
    const wrapper = mount(AddPanel, {
      localVue,
      store: vuexStore,
      stubs: { transition: false },
    });

    const expectedText = [
      T.problemCreatorCase,
      T.problemCreatorGroup,
      T.problemCreatorMultipleCases,
    ];

    await Vue.nextTick();

    const tabs = wrapper.findAll('.nav-link');
    expect(tabs.length).toBe(expectedText.length);
    tabs.wrappers.forEach((tab, index) => {
      expect(tab.text()).toBe(expectedText[index]);
    });
  });
});
