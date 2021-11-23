import { shallowMount, createLocalVue } from '@vue/test-utils';

import Tabs from './Tabs.vue';
import BootstrapVue, { IconsPlugin } from 'bootstrap-vue';
import T from '../../../lang';

const localVue = createLocalVue();
localVue.use(BootstrapVue);
localVue.use(IconsPlugin);

describe('Tabs.vue', () => {
  it('Should contain all 4 tabs', async () => {
    const wrapper = shallowMount(Tabs, { localVue });

    const buttons = wrapper.findAll('span');
    const expectedText = [
      T.problemCreatorStatement,
      T.problemCreatorCode,
      T.problemCreatorTestCases,
      T.problemCreatorSolution,
    ];

    for (let i = 0; i < buttons.length; i++) {
      expect(buttons.at(i).text()).toBe(expectedText[i]);
    }
  });
});
