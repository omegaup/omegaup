import { mount } from '@vue/test-utils';
import expect from 'expect';

import T from '../../lang';

import problem_FilterTags from './FilterTags.vue';

describe('Filter.vue', () => {
  it('Should handle empty list of tags', async () => {
    const wrapper = mount(problem_FilterTags, {
      propsData: {
        tags: <string[]>[
          'problemTagMatrices',
          'problemTagDiophantineEquations',
          'problemTagInputAndOutput',
          'problemTagArrays',
        ],
        publicTags: <string[]>[
          'problemTagConditionals',
          'problemTagLoops',
          'problemTagFunctions',
          'problemTagCharsAndStrings',
          'problemTagSimulation',
          'problemTagAnalyticGeometry',
        ],
      },
    });

    expect(wrapper.text()).toContain(T.problemTagMatrices);
    expect(wrapper.text()).not.toContain(T.problemTagConditionals);

    expect(wrapper.find('input[value="problemTagArrays').exists()).toBe(true);
  });
});
