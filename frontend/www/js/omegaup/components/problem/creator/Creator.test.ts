import { shallowMount } from '@vue/test-utils';

import Creator from './Creator.vue';

describe('Creator.vue', () => {
  it('Should find "Creator path test"', async () => {
    const wrapper = shallowMount(Creator);

    expect(wrapper.find('div').text()).toBe('Creator Path Test');
  });
});
