import { mount } from '@vue/test-utils';

import T from '../../lang';
import type { types } from '../../api_types';

import problem_FilterTags from './FilterTags.vue';

describe('Filter.vue', () => {
  it('Should handle list of tags', async () => {
    const wrapper = mount(problem_FilterTags, {
      propsData: {
        tags: [
          { name: 'problemTagMatrices', problemCount: 5 },
          { name: 'problemTagDiophantineEquations', problemCount: 4 },
          { name: 'problemTagInputAndOutput', problemCount: 3 },
          { name: 'problemTagArrays', problemCount: 2 },
        ] as types.TagWithProblemCount[],
        publicQualityTags: [
          { name: 'problemTagConditionals', problemCount: 1 },
          { name: 'problemTagLoops', problemCount: 1 },
          { name: 'problemTagFunctions', problemCount: 1 },
          { name: 'problemTagCharsAndStrings', problemCount: 1 },
          { name: 'problemTagSimulation', problemCount: 1 },
          { name: 'problemTagAnalyticGeometry', problemCount: 1 },
        ] as types.TagWithProblemCount[],
      },
    });

    expect(wrapper.text()).toContain(T.problemTagMatrices);
    expect(wrapper.text()).not.toContain(T.problemTagConditionals);

    expect(wrapper.find('input[value="problemTagArrays"]').exists()).toBe(true);
    expect(wrapper.find('input[value="problemTagConditionals"]').exists()).toBe(
      false,
    );
  });
});
