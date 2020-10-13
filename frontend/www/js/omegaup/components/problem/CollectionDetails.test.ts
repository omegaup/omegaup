import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import collection_Details from './CollectionDetails.vue';

describe('CollectionDetails.vue', () => {
  it('Should handle empty details of problem collection', async () => {
    const wrapper = shallowMount(collection_Details, {
      propsData: {
        data: {
          type: 'problemLevelBasicIntroductionToProgramming',
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
        } as types.CollectionDetailsPayload,
      },
    });

    expect(wrapper.text()).toContain(
      T.problemLevelBasicIntroductionToProgramming,
    );
  });
});
