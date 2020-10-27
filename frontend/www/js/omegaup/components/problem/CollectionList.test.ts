import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import problem_CollectionList from './CollectionList.vue';

describe('CollectionList.vue', () => {
  it('Should handle empty details of problem list collection', async () => {
    const wrapper = shallowMount(problem_CollectionList, {
      propsData: {
        data: {
          level: 'problemLevelBasicIntroductionToProgramming',
          collection: [
            { alias: 'problemTagMatrices' },
            { alias: 'problemTagDiophantineEquations' },
            { alias: 'problemTagInputAndOutput' },
            { alias: 'problemTagArrays' },
          ],
          publicTags: <string[]>[
            'problemTagConditionals',
            'problemTagLoops',
            'problemTagFunctions',
            'problemTagCharsAndStrings',
            'problemTagSimulation',
            'problemTagAnalyticGeometry',
          ],
        } as types.CollectionDetailsByLevelPayload,
      },
    });

    expect(wrapper.text()).toContain(
      T.problemLevelBasicIntroductionToProgramming,
    );
  });
});
