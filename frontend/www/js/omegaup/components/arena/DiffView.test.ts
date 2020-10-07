import { shallowMount } from '@vue/test-utils';
import expect from 'expect';

import arena_DiffView from './DiffView.vue';

describe('DiffView.vue', () => {
  it('Should handle diffs', async () => {
    const wrapper = shallowMount(arena_DiffView, {
      propsData: {
        left: 'hello',
        right: 'hello',
      },
    });

    expect(wrapper.text()).toBe('hellohello');
  });
});
