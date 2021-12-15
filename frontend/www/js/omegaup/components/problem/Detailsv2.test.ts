import { shallowMount, createLocalVue } from '@vue/test-utils';
import { types } from '../../api_types';
import T from '../../lang';
import problem_Details from './Detailsv2.vue';

import BootstrapVue, { BTab } from 'bootstrap-vue';

const localVue = createLocalVue();
localVue.use(BootstrapVue);

describe('Detailsv2.vue', () => {
  const problem: types.ArenaCourseCurrentProblem = {
    alias: 'test-problem',
    title: 'Test problem',
  };

  it('Should show the tabs', () => {
    const wrapper = shallowMount(problem_Details, {
      propsData: {
        problem,
      },
      localVue,
    });

    const tabs = wrapper.findAllComponents(BTab);
    const expectedTabs = [T.wordsProblem, T.wordsRuns, T.wordsClarifications];
    expect(expectedTabs.length).toBe(tabs.length);
    for (let i = 0; i < expectedTabs.length; i++) {
      expect(tabs.at(i).attributes('title')).toBe(expectedTabs[i]);
    }
  });
});
