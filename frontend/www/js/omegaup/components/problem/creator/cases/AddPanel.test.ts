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

  const testGroup = 'testgroup';

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

    await wrapper.setData({ tab: 'group' });

    await wrapper.find('input[name="group-name"]').setValue(testGroup);

    expect(
      (wrapper.find('input[name="group-name"]').element as HTMLInputElement)
        .value,
    ).toBe(testGroup);

    await (wrapper.vm as any).addItemToStore();

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(1);
    expect(groups[0].name).toBe(testGroup);
    expect(groups[0].autoPoints).toBe(true);
    expect(groups[0].points).toBe(0);
    expect(groups[0].ungroupedCase).toBe(false);

    const groupId = groups[0].groupID;

    await wrapper.setData({ tab: 'case' });

    await wrapper.find('input[name="case-name"]').setValue('groupedtestcase');
    await wrapper.find('input[name="case-points"]').setValue(10);
    await wrapper.find('select[name="case-group"]').setValue(groupId);

    await wrapper.find('form').trigger('submit.prevent');

    const [groupedCase] = store.state.casesStore.groups[0].cases;
    expect(groupedCase.name).toBe('groupedtestcase');
    expect(groupedCase.groupID).toBe(groupId);
    expect(groupedCase.points).toBe(100);
  });

  it('Should add an ungrouped case to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    await wrapper.setData({ tab: 'case' });

    await wrapper.find('input[name="case-name"]').setValue('testcase');
    await wrapper.find('input[name="case-points"]').setValue(50);

    expect(
      (wrapper.find('input[name="case-name"]').element as HTMLInputElement)
        .value,
    ).toBe('testcase');
    expect(
      (wrapper.find('input[name="case-points"]').element as HTMLInputElement)
        .value,
    ).toBe('50');

    await wrapper.find('form').trigger('submit.prevent');

    const store = wrapper.vm.$store as Store<StoreState>;
    const [ungroupedCase] = store.state.casesStore.groups;

    expect(ungroupedCase.name).toBe('testcase');
    expect(ungroupedCase.autoPoints).toBe(true);
    expect(ungroupedCase.points).toBe(100);
    expect(ungroupedCase.ungroupedCase).toBe(true);
  });

  it('Should add multiple ungrouped cases to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    await wrapper.setData({ tab: 'multiplecases' });

    await wrapper.find('input[name="multiple-cases-prefix"]').setValue('test');
    await wrapper.find('input[name="multiple-cases-suffix"]').setValue('case');
    await wrapper.find('input[name="multiple-cases-count"]').setValue(10);

    expect(
      (
        wrapper.find('input[name="multiple-cases-prefix"]')
          .element as HTMLInputElement
      ).value,
    ).toBe('test');
    expect(
      (
        wrapper.find('input[name="multiple-cases-suffix"]')
          .element as HTMLInputElement
      ).value,
    ).toBe('case');
    expect(
      (
        wrapper.find('input[name="multiple-cases-count"]')
          .element as HTMLInputElement
      ).value,
    ).toBe('10');

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
