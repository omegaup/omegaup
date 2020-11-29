import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import problem_FilterTags from './FilterTags.vue';

describe('Filter.vue', () => {
  it('Should handle list of tags', async () => {
    const wrapper = mount(problem_FilterTags, {
      propsData: {
        tags: <{ alias: string; total: number }[]>[
          { alias: 'problemTagMatrices', total: 5 },
          { alias: 'problemTagDiophantineEquations', total: 4 },
          { alias: 'problemTagInputAndOutput', total: 3 },
          { alias: 'problemTagArrays', total: 2 },
        ],
        publicQualityTags: <{ alias: string; total: number }[]>[
          { alias: 'problemTagConditionals', total: 1 },
          { alias: 'problemTagLoops', total: 1 },
          { alias: 'problemTagFunctions', total: 1 },
          { alias: 'problemTagCharsAndStrings', total: 1 },
          { alias: 'problemTagSimulation', total: 1 },
          { alias: 'problemTagAnalyticGeometry', total: 1 },
        ],
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
