import { createLocalVue, mount } from '@vue/test-utils';

import AddWindow from './AddWindow.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../../lang';
import Vue from 'vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('AddWindow.vue', () => {
  it('Should contain 3 tabs', async () => {
    const wrapper = mount(AddWindow, {
      localVue,
      stubs: { transition: false },
    });

    const expectedText = [
      T.problemCreatorCase,
      T.problemCreatorMultipleCases,
      T.problemCreatorGroup,
    ];

    await Vue.nextTick();

    const tabs = wrapper.findAll('.nav-link');
    expect(tabs.length).toBe(expectedText.length);
    tabs.wrappers.forEach((tab, index) => {
      expect(tab.text()).toBe(expectedText[index]);
    });
  });
});
