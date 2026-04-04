jest.mock('../../../../third_party/js/diff_match_patch.js');

import { shallowMount } from '@vue/test-utils';

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
