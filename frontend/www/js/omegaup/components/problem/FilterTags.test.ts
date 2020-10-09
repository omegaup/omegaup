import { mount } from '@vue/test-utils';
import expect from 'expect';

import { types } from '../../api_types';
import T from '../../lang';

import collection_Filter_Tags from './FilterTags.vue';

describe('Filter.vue', () => {
  it('Should handle empty list of tags', async () => {
    const wrapper = mount(collection_Filter_Tags, {
      propsData: {
        collection: <types.CheckedTag[]>[
          { tagname: 'problemTagMatrices', checked: false },
          { tagname: 'problemTagDiophantineEquations', checked: false },
          { tagname: 'problemTagInputAndOutput', checked: false },
          { tagname: 'problemTagArrays', checked: false },
        ],
        anotherTags: <types.CheckedTag[]>[
          { tagname: 'problemTagConditionals', checked: false },
          { tagname: 'problemTagLoops', checked: false },
          { tagname: 'problemTagFunctions', checked: false },
          { tagname: 'problemTagCharsAndStrings', checked: false },
          { tagname: 'problemTagSimulation', checked: false },
          { tagname: 'problemTagAnalyticGeometry', checked: false },
        ],
      },
    });

    expect(wrapper.text()).toContain(T.problemTagMatrices);
    expect(wrapper.text()).not.toContain(T.problemTagConditionals);

    expect(wrapper.find('#problemTagArrays').exists());
  });
});
