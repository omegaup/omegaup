import { shallowMount } from '@vue/test-utils';
import common_Typeahead from './Typeahead.vue';

describe('Typeahead.vue', () => {
  it('Should handle empty existing options list', async () => {
    const wrapper = shallowMount(common_Typeahead, {
      propsData: {
        existingOptions: [],
      },
    });

    expect(wrapper.vm.$data.selectedOptions).toStrictEqual([]);
  });
});
