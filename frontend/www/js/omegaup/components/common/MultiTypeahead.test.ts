import { shallowMount } from '@vue/test-utils';
import common_MultiTypeahead from './MultiTypeahead.vue';

describe('MultiTypeahead.vue', () => {
  it('Should handle empty existing options list', async () => {
    const wrapper = shallowMount(common_MultiTypeahead, {
      propsData: {
        existingOptions: [],
      },
    });

    expect(wrapper.vm.$data.selectedOptions).toStrictEqual([]);
  });
});
