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
  it('Should add a group to the store', async () => {
    const wrapper: Wrapper<AddPanel> = mount(AddPanel, {
      localVue,
      store: vuexStore,
    });

    wrapper.setData({ tab: 'group' });

    await Vue.nextTick();

    const nameInput = wrapper.find('input[name="group-name"]');
    const pointsInput = wrapper.find('input[name="group-points"]');

    await nameInput.setValue('groupname');
    await pointsInput.setValue(10);

    const updatedNameInput = wrapper.find('input[name="group-name"]')
      .element as HTMLInputElement;
    const updatedPointsInput = wrapper.find('input[name="group-points"]')
      .element as HTMLInputElement;

    expect(updatedNameInput.value).toBe('groupname');
    expect(updatedPointsInput.value).toBe('10');

    await (wrapper.vm as any).addItemToStore();

    const store = wrapper.vm.$store as Store<StoreState>;
    const groups = store.state.casesStore.groups;

    expect(groups.length).toBe(1);
    expect(groups[0].name).toBe('groupname');
    expect(groups[0].autoPoints).toBe(false);
    expect(groups[0].points).toBe(10);
    expect(groups[0].ungroupedCase).toBe(false);
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
